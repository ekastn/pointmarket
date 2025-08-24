<?php
// This partial can be included to display NLP results.
// It provides the HTML structure and the necessary JavaScript function `renderNlpResults`.
// The container ID can be customized by passing 'containerId' and 'bodyId' in the data array.
$containerId = $containerId ?? 'vark-results-container';
$bodyId = $bodyId ?? 'vark-results-body';
?>

<div id="<?php echo htmlspecialchars($containerId); ?>" class="card mt-4" style="display: none;">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Hasil Analisis</h5>
    </div>
    <div class="card-body" id="<?php echo htmlspecialchars($bodyId); ?>">
        <!-- Results will be loaded here by JavaScript -->
    </div>
</div>

<script>
    // Ensure the functions are defined only once, in case the partial is included multiple times.
    if (typeof renderVarkResults !== 'function') {
        /**
         * Renders the NLP analysis results into a specified container.
         * @param {object} data The analysis data from the API.
         * @param {string} containerId The ID of the element to render the results into.
         */
        function renderVarkResults(data, containerId) {
            const container = document.getElementById(containerId);

            const qualityMetrics = Object.keys(data.text_stats);

            let html = '';

            if (data.learning_style) {
                const varkscore = data.learning_style.scores;
                const styleType = data.learning_style.type;
                const styleLabel = data.learning_style.label;
                html += `
                    <h6>Preferensi Belajar:</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h4><i class="${getVARKIcon(styleLabel)} me-2"></i>${styleLabel}</h4>
                                    <p class="text-muted mb-0">Tipe: ${styleType}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">Skor Gabungan VARK:</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Visual
                                            <span class="badge bg-primary rounded-pill">${varkscore.visual.toFixed(1)}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Auditory
                                            <span class="badge bg-primary rounded-pill">${varkscore.auditory.toFixed(1)}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Reading
                                            <span class="badge bg-primary rounded-pill">${varkscore.reading.toFixed(1)}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Kinesthetic
                                            <span class="badge bg-primary rounded-pill">${varkscore.kinesthetic.toFixed(1)}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                `;  
                
            }

            html += `
                <div class="row mb-4">
                   ${qualityMetrics.map(metric => {
                       const value = data.text_stats[metric] || 0;
                       const label = metric.replace(/_/g, ' ');
                       return `
                           <div class="col-md-4 mb-3">
                               <div class="card h-100 border-0 shadow-sm">
                                   <div class="card-body text-center">
                                       <h3 class="text-capitalize">${label}</h3>
                                       <span class="score-badge">
                                           ${value.toFixed(2)}
                                       </span>
                                   </div>
                               </div>
                           </div>
                       `;
                   }).join('')}
                 </div>
                
                <h6>Kata Kunci:</h6>
                <div class="mb-4">
            `;
            
            // Add keywords
            if (data.keywords && data.keywords.length > 0) {
                data.keywords.forEach(keyword => {
                    html += `<span class="badge bg-primary me-2 mb-2">${keyword}</span>`;
                });
            } else {
                html += `<p class="text-muted">Tidak ada kata kunci yang signifikan ditemukan.</p>`;
            }
            
            html += `
                </div>
                
                <h6>Kalimat Penting:</h6>
                <div class="mb-4">
            `;
            
            // Add key sentences
            if (data.key_sentences && data.key_sentences.length > 0) {
                html += `<ul class="list-group">`;
                data.key_sentences.forEach(sentence => {
                    html += `<li class="list-group-item">${sentence}</li>`;
                });
                html += `</ul>`;
            } else {
                html += `<p class="text-muted">Tidak ada kalimat penting yang ditemukan.</p>`;
            }

            html += `
                </div>
            `;
            
            if (!container) {
                return html;;
            } else {
                container.innerHTML = html;
                return container;
            }
        }

        function getVARKIcon(type) {
            if (!type) return 'fas fa-question-circle';
            switch (type.toLowerCase()) {
                case 'visual': return 'fas fa-eye';
                case 'auditory': return 'fas fa-volume-up';
                case 'reading': return 'fas fa-book-open';
                case 'kinesthetic': return 'fas fa-running';
                default: return 'fas fa-question-circle';
            }
        }
    }
</script>
