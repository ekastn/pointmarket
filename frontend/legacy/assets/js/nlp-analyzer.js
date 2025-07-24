/**
 * NLP Frontend Integration untuk POINTMARKET
 * 
 * JavaScript library untuk mengintegrasikan NLP analysis
 * dengan form assignments dan quiz
 */

class NLPAnalyzer {
    constructor() {
        this.apiUrl = 'api/nlp-analysis.php';
        this.isAnalyzing = false;
        this.currentAnalysis = null;
        this.version = Date.now(); // Cache busting
        
        // Initialize UI elements
        this.initializeUI();
        this.bindEvents();
    }
    
    /**
     * Initialize UI elements
     */
    initializeUI() {
        // Create analysis button if not exists
        const textareas = document.querySelectorAll('textarea[data-nlp="true"]');
        textareas.forEach(textarea => {
            this.createAnalysisButton(textarea);
            this.createResultsContainer(textarea);
        });
    }
    
    /**
     * Create analysis button for textarea
     */
    createAnalysisButton(textarea) {
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'nlp-button-container mt-2';
        
        const analyzeButton = document.createElement('button');
        analyzeButton.type = 'button';
        analyzeButton.className = 'btn btn-outline-primary btn-sm';
        analyzeButton.innerHTML = '<i class="fas fa-brain me-1"></i>Analisis AI';
        analyzeButton.onclick = () => this.analyzeText(textarea);
        
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'btn btn-outline-secondary btn-sm ms-2';
        clearButton.innerHTML = '<i class="fas fa-eraser me-1"></i>Clear';
        clearButton.onclick = () => this.clearAnalysis(textarea);
        
        buttonContainer.appendChild(analyzeButton);
        buttonContainer.appendChild(clearButton);
        
        textarea.parentNode.insertBefore(buttonContainer, textarea.nextSibling);
    }
    
    /**
     * Create results container
     */
    createResultsContainer(textarea) {
        const container = document.createElement('div');
        container.className = 'nlp-results-container mt-3';
        container.id = `nlp-results-${textarea.id}`;
        container.style.display = 'none';
        
        const buttonContainer = textarea.parentNode.querySelector('.nlp-button-container');
        buttonContainer.parentNode.insertBefore(container, buttonContainer.nextSibling);
    }
    
    /**
     * Bind events
     */
    bindEvents() {
        // Auto-analyze on typing (debounced)
        const textareas = document.querySelectorAll('textarea[data-nlp="true"]');
        textareas.forEach(textarea => {
            let timeout;
            textarea.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    if (textarea.value.length > 50) {
                        this.analyzeText(textarea, false); // Don't save auto-analysis
                    }
                }, 3000); // 3 second delay
            });
        });
    }
    
    /**
     * Analyze text using NLP API
     */
    async analyzeText(textarea, saveResult = true) {
        const text = textarea.value.trim();
        
        if (text.length < 10) {
            this.showError(textarea, 'Teks terlalu pendek untuk dianalisis (minimal 10 karakter)');
            return;
        }
        
        if (this.isAnalyzing) {
            return;
        }
        
        this.isAnalyzing = true;
        this.showLoading(textarea);
        
        try {
            const response = await fetch(`${this.apiUrl}?v=${this.version}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    text: text,
                    context: this.getContext(textarea),
                    assignment_id: this.getAssignmentId(textarea),
                    save_result: saveResult
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Response is not JSON:', text);
                throw new Error('Response is not valid JSON');
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.showResults(textarea, result.data);
                this.currentAnalysis = result.data;
            } else {
                this.showError(textarea, result.error || 'Analisis gagal');
            }
            
        } catch (error) {
            console.error('NLP Analysis Error:', error);
            this.showError(textarea, 'Terjadi kesalahan saat menganalisis teks');
        } finally {
            this.isAnalyzing = false;
            this.hideLoading(textarea);
        }
    }
    
    /**
     * Show loading state
     */
    showLoading(textarea) {
        const button = textarea.parentNode.querySelector('.nlp-button-container button');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menganalisis...';
    }
    
    /**
     * Hide loading state
     */
    hideLoading(textarea) {
        const button = textarea.parentNode.querySelector('.nlp-button-container button');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-brain me-1"></i>Analisis AI';
    }
    
    /**
     * Show analysis results
     */
    showResults(textarea, analysis) {
        const container = document.getElementById(`nlp-results-${textarea.id}`);
        container.style.display = 'block';
        
        container.innerHTML = `
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Hasil Analisis AI</h6>
                </div>
                <div class="card-body">
                    <!-- Overall Score -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="display-4 ${this.getScoreColor(analysis.total_score)}">${analysis.total_score}</div>
                                <small class="text-muted">Total Score</small>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="text-center">
                                        <div class="h5 ${this.getScoreColor(analysis.grammar_score)}">${analysis.grammar_score}</div>
                                        <small>Grammar</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="text-center">
                                        <div class="h5 ${this.getScoreColor(analysis.keyword_score)}">${analysis.keyword_score}</div>
                                        <small>Keywords</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="text-center">
                                        <div class="h5 ${this.getScoreColor(analysis.structure_score)}">${analysis.structure_score}</div>
                                        <small>Structure</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress bars -->
                    <div class="mb-3">
                        ${this.createProgressBar('Grammar', analysis.grammar_score)}
                        ${this.createProgressBar('Keywords', analysis.keyword_score)}
                        ${this.createProgressBar('Structure', analysis.structure_score)}
                        ${this.createProgressBar('Readability', analysis.readability_score)}
                    </div>
                    
                    <!-- Text Statistics -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-font me-1"></i>Jumlah Kata: <strong>${analysis.word_count}</strong>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-paragraph me-1"></i>Jumlah Kalimat: <strong>${analysis.sentence_count}</strong>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Feedback -->
                    <div class="mb-3">
                        <h6><i class="fas fa-comments me-2"></i>Feedback</h6>
                        <div class="alert alert-light">
                            ${analysis.feedback.map(f => `<div class="mb-1">${f}</div>`).join('')}
                        </div>
                    </div>
                    
                    <!-- Personalized Feedback -->
                    ${analysis.personalized_feedback ? `
                        <div class="mb-3">
                            <h6><i class="fas fa-user-cog me-2"></i>Feedback Personal</h6>
                            <div class="alert alert-info">
                                ${analysis.personalized_feedback.map(f => `<div class="mb-1">${f}</div>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Action buttons -->
                    <div class="text-end">
                        <button class="btn btn-sm btn-outline-success" onclick="nlpAnalyzer.showHistory()">
                            <i class="fas fa-history me-1"></i>Riwayat
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="nlpAnalyzer.showTips()">
                            <i class="fas fa-lightbulb me-1"></i>Tips
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Create progress bar HTML
     */
    createProgressBar(label, score) {
        const color = this.getProgressColor(score);
        return `
            <div class="mb-2">
                <div class="d-flex justify-content-between">
                    <small>${label}</small>
                    <small>${score}%</small>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-${color}" role="progressbar" 
                         style="width: ${score}%" aria-valuenow="${score}" 
                         aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        `;
    }
    
    /**
     * Get score color class
     */
    getScoreColor(score) {
        if (score >= 80) return 'text-success';
        if (score >= 60) return 'text-warning';
        return 'text-danger';
    }
    
    /**
     * Get progress bar color
     */
    getProgressColor(score) {
        if (score >= 80) return 'success';
        if (score >= 60) return 'warning';
        return 'danger';
    }
    
    /**
     * Show error message
     */
    showError(textarea, message) {
        const container = document.getElementById(`nlp-results-${textarea.id}`);
        container.style.display = 'block';
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>${message}
            </div>
        `;
    }
    
    /**
     * Clear analysis results
     */
    clearAnalysis(textarea) {
        const container = document.getElementById(`nlp-results-${textarea.id}`);
        container.style.display = 'none';
        container.innerHTML = '';
        this.currentAnalysis = null;
    }
    
    /**
     * Get context from textarea attributes
     */
    getContext(textarea) {
        return textarea.getAttribute('data-context') || 'assignment';
    }
    
    /**
     * Get assignment ID from form or textarea attributes
     */
    getAssignmentId(textarea) {
        const form = textarea.closest('form');
        const assignmentIdInput = form ? form.querySelector('input[name="assignment_id"]') : null;
        return assignmentIdInput ? assignmentIdInput.value : null;
    }
    
    /**
     * Show analysis history
     */
    async showHistory() {
        try {
            const response = await fetch(`${this.apiUrl}?action=history&limit=5&v=${this.version}`);
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                this.showHistoryModal(result.data);
            } else {
                alert('Belum ada riwayat analisis');
            }
        } catch (error) {
            console.error('Error loading history:', error);
            alert('Gagal memuat riwayat');
        }
    }
    
    /**
     * Show history modal
     */
    showHistoryModal(history) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Riwayat Analisis NLP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${history.map(item => `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>${item.assignment_title || 'Assignment'}</h6>
                                            <p class="mb-1"><small>${item.original_text.substring(0, 100)}...</small></p>
                                            <small class="text-muted">${item.created_at}</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="h4 ${this.getScoreColor(item.total_score)}">${item.total_score}</div>
                                            <small>Total Score</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Remove modal after hiding
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }
    
    /**
     * Show writing tips
     */
    showTips() {
        const tips = [
            "📝 Mulai dengan kalimat yang jelas dan mudah dipahami",
            "🎯 Gunakan kata kunci yang relevan dengan topik",
            "📊 Susun paragraf dengan struktur yang logis",
            "🔗 Gunakan kata penghubung antar kalimat",
            "✅ Periksa tata bahasa dan ejaan sebelum submit",
            "📖 Berikan contoh untuk memperjelas penjelasan",
            "🎭 Gunakan kalimat aktif dibanding kalimat pasif",
            "⚖️ Jaga keseimbangan panjang kalimat"
        ];
        
        const randomTips = tips.sort(() => 0.5 - Math.random()).slice(0, 4);
        
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tips Menulis Yang Baik</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb me-2"></i>Tips untuk Anda:</h6>
                            ${randomTips.map(tip => `<div class="mb-2">${tip}</div>`).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Remove modal after hiding
        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });
    }
}

// Initialize NLP Analyzer when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.nlpAnalyzer = new NLPAnalyzer();
});

// Utility functions
function enableNLPForTextarea(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (textarea) {
        textarea.setAttribute('data-nlp', 'true');
        if (window.nlpAnalyzer) {
            window.nlpAnalyzer.createAnalysisButton(textarea);
            window.nlpAnalyzer.createResultsContainer(textarea);
        }
    }
}

function disableNLPForTextarea(textareaId) {
    const textarea = document.getElementById(textareaId);
    if (textarea) {
        textarea.removeAttribute('data-nlp');
        // Remove NLP UI elements
        const buttonContainer = textarea.parentNode.querySelector('.nlp-button-container');
        const resultsContainer = textarea.parentNode.querySelector('.nlp-results-container');
        if (buttonContainer) buttonContainer.remove();
        if (resultsContainer) resultsContainer.remove();
    }
}
