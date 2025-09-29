-- name: GetStudentStatistic :one
SELECT
	CAST(
        COALESCE(
            (
				SELECT
					us.total_points
				FROM
					user_stats us
				WHERE
					us.user_id = u.id
            ),
			0
        ) AS SIGNED	
	) AS total_points,
    CAST(
        COALESCE(
            (
                SELECT
                    COUNT(*)
                FROM
                    student_assignments sa
                    JOIN students s_sa ON s_sa.user_id = u.id
                WHERE
                    sa.student_id = s_sa.student_id
                    AND sa.status = 'completed'
            ),
            0
        ) AS SIGNED
    ) AS completed_assignments,
	CAST(
        COALESCE(
            (
				SELECT
					sqr.total_score
				FROM
					student_questionnaire_likert_results sqr
					JOIN questionnaires q ON sqr.questionnaire_id = q.id
                    JOIN students s_mslq ON s_mslq.user_id = u.id
				WHERE
					sqr.student_id = s_mslq.student_id
					AND q.type = 'MSLQ'
				ORDER BY
					sqr.created_at DESC
				LIMIT
					1
            ),
            0
        ) AS FLOAT
    ) AS mslq_score,
	CAST(
        COALESCE(
            (
				SELECT
					sqr.total_score
				FROM
					student_questionnaire_likert_results sqr
					JOIN questionnaires q ON sqr.questionnaire_id = q.id
                    JOIN students s_ams ON s_ams.user_id = u.id
				WHERE
					sqr.student_id = s_ams.student_id
					AND q.type = 'AMS'
				ORDER BY
					sqr.created_at DESC
				LIMIT
					1
            ),
            0
        ) AS FLOAT
    ) AS ams_score
FROM
    users u
WHERE
    u.id = ?
    AND u.role = 'siswa';

-- name: GetStudentLearningStyle :one
SELECT
    sls.*
FROM
    student_learning_styles sls
    JOIN students s ON s.student_id = sls.student_id
WHERE
    s.user_id = ?
ORDER BY
    sls.created_at DESC
LIMIT
    1;

-- name: GetTeacherStatistic :one
SELECT
    (
        SELECT
            COUNT(*)
        FROM
            assignments a
            JOIN courses c ON a.course_id = c.id
        WHERE
            c.owner_id = sqlc.arg(teacher_id)
    ) AS total_assignments,
    (
        SELECT
            COUNT(*)
        FROM
            courses c
        WHERE
            c.owner_id = sqlc.arg(teacher_id)
    ) AS total_courses,
    (
        SELECT
            COUNT(*)
        FROM
            quizzes q
            JOIN courses c ON q.course_id = c.id
        WHERE
            c.owner_id = sqlc.arg(teacher_id)
    ) AS total_quizzes,
    (
        SELECT
            COUNT(*)
        FROM
            users
        WHERE
            role = 'siswa'
    ) AS total_students;

-- name: GetAdminStatistic :one
SELECT
    (
        SELECT
            COUNT(*)
        FROM
            users
    ) AS total_users,
    (
        SELECT
            COUNT(*)
        FROM
            users
        WHERE
            role = 'siswa'
    ) AS total_students,
    (
        SELECT
            COUNT(*)
        FROM
            users
        WHERE
            role = 'guru'
    ) AS total_teachers,
    (
        SELECT
            COUNT(*)
        FROM
            points_transactions
    ) AS total_points_transactions,
    (
        SELECT
            COUNT(*)
        FROM
            courses
    ) AS total_courses,
    (
        SELECT
            COUNT(*)
        FROM
            products
    ) AS total_products,
    (
        SELECT
            COUNT(*)
        FROM
            missions
    ) AS total_missions,
    (
        SELECT
            COUNT(*)
        FROM
            badges
    ) AS total_badges;

-- name: GetTeacherCourseInsights :many
-- Aggregates latest AMS, MSLQ, and VARK per course for a given teacher (owner)
SELECT
    c.id AS course_id,
    c.title AS course_title,
    CAST(COALESCE(mslq.avg_score, 0) AS DECIMAL(10,4)) AS avg_mslq,
    CAST(COALESCE(ams.avg_score, 0) AS DECIMAL(10,4)) AS avg_ams,
    CAST(COALESCE(vark.avg_visual, 0) AS DECIMAL(10,4)) AS avg_visual,
    CAST(COALESCE(vark.avg_auditory, 0) AS DECIMAL(10,4)) AS avg_auditory,
    CAST(COALESCE(vark.avg_reading, 0) AS DECIMAL(10,4)) AS avg_reading,
    CAST(COALESCE(vark.avg_kinesthetic, 0) AS DECIMAL(10,4)) AS avg_kinesthetic
FROM courses c
LEFT JOIN (
    SELECT sc.course_id, AVG(r.total_score) AS avg_score
    FROM student_courses sc
    JOIN (
        SELECT r1.student_id, r1.total_score
        FROM student_questionnaire_likert_results r1
        JOIN questionnaires q1 ON r1.questionnaire_id = q1.id
        JOIN (
            SELECT r2.student_id, MAX(r2.created_at) AS max_created_at
            FROM student_questionnaire_likert_results r2
            JOIN questionnaires q2 ON r2.questionnaire_id = q2.id
            WHERE q2.type = 'MSLQ'
            GROUP BY r2.student_id
        ) latest ON latest.student_id = r1.student_id AND r1.created_at = latest.max_created_at
        WHERE q1.type = 'MSLQ'
    ) r ON r.student_id = sc.student_id
    GROUP BY sc.course_id
) mslq ON mslq.course_id = c.id
LEFT JOIN (
    SELECT sc.course_id, AVG(r.total_score) AS avg_score
    FROM student_courses sc
    JOIN (
        SELECT r1.student_id, r1.total_score
        FROM student_questionnaire_likert_results r1
        JOIN questionnaires q1 ON r1.questionnaire_id = q1.id
        JOIN (
            SELECT r2.student_id, MAX(r2.created_at) AS max_created_at
            FROM student_questionnaire_likert_results r2
            JOIN questionnaires q2 ON r2.questionnaire_id = q2.id
            WHERE q2.type = 'AMS'
            GROUP BY r2.student_id
        ) latest ON latest.student_id = r1.student_id AND r1.created_at = latest.max_created_at
        WHERE q1.type = 'AMS'
    ) r ON r.student_id = sc.student_id
    GROUP BY sc.course_id
) ams ON ams.course_id = c.id
LEFT JOIN (
    SELECT sc.course_id,
           AVG(COALESCE(sls.score_visual, 0))      AS avg_visual,
           AVG(COALESCE(sls.score_auditory, 0))    AS avg_auditory,
           AVG(COALESCE(sls.score_reading, 0))     AS avg_reading,
           AVG(COALESCE(sls.score_kinesthetic, 0)) AS avg_kinesthetic
    FROM student_courses sc
    JOIN (
        SELECT sl1.*
        FROM student_learning_styles sl1
        JOIN (
            SELECT student_id, MAX(created_at) AS max_created_at
            FROM student_learning_styles
            GROUP BY student_id
        ) latest_sls ON sl1.student_id = latest_sls.student_id AND sl1.created_at = latest_sls.max_created_at
    ) sls ON sls.student_id = sc.student_id
    GROUP BY sc.course_id
) vark ON vark.course_id = c.id
WHERE c.owner_id = ?
ORDER BY c.created_at DESC
LIMIT ?;
