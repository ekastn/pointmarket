class NLPAnalyzer {
    constructor(apiClient) {
        this.apiClient = apiClient;
        this.typingTimer;
        this.doneTypingInterval = 3000; // 3 seconds
        this.textarea = document.getElementById('text_to_analyze');
        this.resultsContainer = document.getElementById('nlp-results-container');
        this.resultsBody = document.getElementById('nlp-results-body');
        this.userStatsContainer = document.getElementById('user-stats');
        this.contextSelect = document.getElementById('context_type');

        if (this.textarea) {
            this.textarea.addEventListener('keyup', () => {
                clearTimeout(this.typingTimer);
                if (this.textarea.value.length >= 10) {
                    this.typingTimer = setTimeout(() => this.analyzeText(this.textarea), this.doneTypingInterval);
                }
            });
            this.textarea.addEventListener('keydown', () => {
                clearTimeout(this.typingTimer);
            });
        }

        this.loadUserStats();
    }

    async analyzeText(textarea) {
        const text = textarea.value;
        const contextType = this.contextSelect.value;

        if (text.length < 10) {
            this.resultsContainer.style.display = 'none';
            return;
        }

        try {
            const response = await this.apiClient.analyzeText(text, contextType);
            if (response.success) {
                this.displayResults(response.data);
                this.loadUserStats(); // Refresh stats after analysis
            } else {
                this.displayError(response.error);
            }
        } catch (error) {
            this.displayError('Network error: ' + error.message);
        }
    }

    displayResults(result) {
        let feedbackHtml = '';
        if (result.feedback) {
            const feedbackArray = JSON.parse(result.feedback);
            feedbackHtml = `<h6><i class="fas fa-comments me-2"></i>Feedback</h6><div class="alert alert-light">`;
            if (Array.isArray(feedbackArray)) {
                feedbackArray.forEach(f => {
                    feedbackHtml += `<div class="mb-1">${this.escapeHtml(f)}</div>`;
                });
            } else {
                feedbackHtml += this.escapeHtml(result.feedback);
            }
            feedbackHtml += `</div>`;
        }

        let personalizedFeedbackHtml = '';
        if (result.personalized_feedback) {
            const personalizedFeedbackArray = JSON.parse(result.personalized_feedback);
            personalizedFeedbackHtml = `<h6><i class="fas fa-user-cog me-2"></i>Feedback Personal</h6><div class="alert alert-info">`;
            if (Array.isArray(personalizedFeedbackArray)) {
                personalizedFeedbackArray.forEach(f => {
                    personalizedFeedbackHtml += `<div class="mb-1">${this.escapeHtml(f)}</div>`;
                });
            } else {
                personalizedFeedbackHtml += this.escapeHtml(result.personalized_feedback);
            }
            personalizedFeedbackHtml += `</div>`;
        }

        const scoresHtml = [
            { label: 'Grammar', score: result.grammar_score },
            { label: 'Keywords', score: result.keyword_score },
            { label: 'Structure', score: result.structure_score },
            { label: 'Readability', score: result.readability_score },
            { label: 'Sentiment', score: result.sentiment_score },
            { label: 'Complexity', score: result.complexity_score },
        ].map(item => {
            const progressColor = (item.score >= 80) ? 'success' : ((item.score >= 60) ? 'warning' : 'danger');
            return `
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <small>${item.label}</small>
                        <small>${item.score.toFixed(1)}%</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-${progressColor}" role="progressbar" 
                             style="width: ${item.score}%" aria-valuenow="${item.score}" 
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            `;
        }).join('');

        this.resultsBody.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4 ${this.getScoreColorClass(result.total_score)}">
                            ${result.total_score.toFixed(1)}
                        </div>
                        <small class="text-muted">Total Score</small>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-6 col-md-4">
                            <div class="text-center">
                                <div class="h5 ${this.getScoreColorClass(result.grammar_score)}">
                                    ${result.grammar_score.toFixed(1)}
                                </div>
                                <small>Grammar</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-center">
                                <div class="h5 ${this.getScoreColorClass(result.keyword_score)}">
                                    ${result.keyword_score.toFixed(1)}
                                </div>
                                <small>Keywords</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-center">
                                <div class="h5 ${this.getScoreColorClass(result.structure_score)}">
                                    ${result.structure_score.toFixed(1)}
                                </div>
                                <small>Structure</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                ${scoresHtml}
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-font me-1"></i>Jumlah Kata: <strong>${result.word_count}</strong>
                    </small>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-paragraph me-1"></i>Jumlah Kalimat: <strong>${result.sentence_count}</strong>
                    </small>
                </div>
            </div>
            
            ${feedbackHtml}
            ${personalizedFeedbackHtml}
        `;
        this.resultsContainer.style.display = 'block';
    }

    displayError(message) {
        this.resultsBody.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Error:</strong> ${this.escapeHtml(message)}
            </div>
        `;
        this.resultsContainer.style.display = 'block';
    }

    clearAnalysis() {
        this.textarea.value = '';
        this.resultsContainer.style.display = 'none';
        this.resultsBody.innerHTML = '';
    }

    async loadUserStats() {
        try {
            const response = await this.apiClient.getNLPStats();
            if (response.success && response.data && response.data.overall && response.data.overall.total_analyses > 0) {
                const stats = response.data.overall;
                this.userStatsContainer.innerHTML = `
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h4 class="text-primary">${stats.total_analyses || 0}</h4>
                            <small class="text-muted">Total Analisis</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-success">${parseFloat(stats.average_score || 0).toFixed(1)}</h4>
                            <small class="text-muted">Rata-rata Score</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-info">${parseFloat(stats.best_score || 0).toFixed(1)}</h4>
                            <small class="text-muted">Score Terbaik</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <small class="text-muted">Grammar Improvement:</small>
                            <h5 class="text-${(stats.grammar_improvement >= 0) ? 'success' : 'danger'}">
                                ${parseFloat(stats.grammar_improvement || 0).toFixed(1)}%
                            </h5>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted">Keyword Improvement:</small>
                            <h5 class="text-${(stats.keyword_improvement >= 0) ? 'success' : 'danger'}">
                                ${parseFloat(stats.keyword_improvement || 0).toFixed(1)}%
                            </h5>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted">Structure Improvement:</small>
                            <h5 class="text-${(stats.structure_improvement >= 0) ? 'success' : 'danger'}">
                                ${parseFloat(stats.structure_improvement || 0).toFixed(1)}%
                            </h5>
                        </div>
                    </div>
                `;
            } else {
                this.userStatsContainer.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <p>Belum ada data analisis NLP</p>
                        <small>Lakukan analisis pertama untuk melihat statistik</small>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading stats:', error);
            this.userStatsContainer.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Info:</strong> Sistem NLP sedang dalam perbaikan.
                    <br><small>Silakan coba lagi nanti atau hubungi administrator jika masalah berlanjut.</small>
                    <br><small class="text-muted">Error: ${this.escapeHtml(error.message)}</small>
                </div>
            `;
        }
    }

    getScoreColorClass(score) {
        if (score >= 80) return 'text-success';
        if (score >= 60) return 'text-warning';
        return 'text-danger';
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            ''': '&#039;'
        };
        return text.replace(/[&<>"]/'/, function(m) { return map[m]; });
    }
}

// Initialize the NLPAnalyzer when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Assuming ApiClient is globally available or can be instantiated here
    // For now, we'll assume it's passed or can be created.
    // You might need to adjust this based on how your ApiClient is exposed.
    const apiClient = new ApiClient(API_BASE_URL); // API_BASE_URL should be defined globally
    window.nlpAnalyzer = new NLPAnalyzer(apiClient);

    // Global functions for example loading and clearing
    window.loadExample = (type) => {
        const examples = {
            good: `Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran. Pertama, teknologi menyediakan akses ke sumber belajar yang tidak terbatas melalui internet. Kedua, aplikasi pembelajaran interaktif memungkinkan siswa untuk belajar dengan cara yang lebih menarik dan efektif. Ketiga, platform digital memfasilitasi komunikasi antara guru dan siswa di luar jam sekolah. Dengan demikian, integrasi teknologi dalam pendidikan bukan hanya trend, tetapi kebutuhan fundamental untuk menciptakan sistem pembelajaran yang adaptif dan berkelanjutan.`,
            
            bad: `teknologi bagus untuk sekolah karena bisa belajar dengan komputer dan internet juga bisa cari materi di google terus bisa cari materi di google terus bisa ngerjain tugas lebih gampang pokoknya teknologi sangat membantu`
        };
        const textarea = document.getElementById('text_to_analyze');
        textarea.value = examples[type];
        window.nlpAnalyzer.analyzeText(textarea); // Trigger analysis immediately
    };

    window.clearAll = () => {
        window.nlpAnalyzer.clearAnalysis();
    };

    window.updateContext = () => {
        // Context is automatically picked up from the select element by NLPAnalyzer
    };
});