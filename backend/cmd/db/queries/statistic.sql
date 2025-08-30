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
