// Dashboard JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar-wrapper').classList.toggle('is-open');
        });
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-dismiss alerts (except demo alert)
    // Demo alert with class 'demo-alert' will remain visible until manually dismissed
    // Other alerts will be auto-dismissed after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible:not(.demo-alert)');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    }
    
    // AI Performance Simulation
    simulateAIPerformance();
    
    // Real-time clock
    updateClock();
    setInterval(updateClock, 1000);
    
    // Statistics counter animation
    animateCounters();
});

// AI Performance Simulation
function simulateAIPerformance() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(function(bar) {
        const targetWidth = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(function() {
            bar.style.transition = 'width 2s ease-in-out';
            bar.style.width = targetWidth;
        }, 500);
    });
}

// Update real-time clock
function updateClock() {
    const clockElement = document.getElementById('realTimeClock');
    if (clockElement) {
        const now = new Date();
        clockElement.textContent = now.toLocaleTimeString('id-ID');
    }
}

// Animate statistics counters
function animateCounters() {
    const counters = document.querySelectorAll('.h5');
    
    counters.forEach(function(counter) {
        const target = parseInt(counter.textContent.replace(/[^0-9]/g, ''));
        if (!isNaN(target)) {
            let current = 0;
            const increment = target / 50;
            
            const timer = setInterval(function() {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                // Format number based on original format
                if (counter.textContent.includes('.')) {
                    counter.textContent = formatPoints(Math.floor(current));
                } else {
                    counter.textContent = Math.floor(current);
                }
            }, 40);
        }
    });
}

// Format numbers with thousand separators
function formatPoints(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Confirmation dialog for dangerous actions
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Show loading spinner
function showLoading(element) {
    const originalContent = element.innerHTML;
    element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    element.disabled = true;
    
    return function() {
        element.innerHTML = originalContent;
        element.disabled = false;
    };
}

// AJAX helper function
function makeAjaxRequest(url, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    callback(null, response);
                } catch (e) {
                    callback('Invalid JSON response', null);
                }
            } else {
                callback('Request failed: ' + xhr.status, null);
            }
        }
    };
    
    xhr.send(JSON.stringify(data));
}

// Form validation helper
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Show success message
function showSuccess(message) {
    showAlert(message, 'success');
}

// Show error message
function showError(message) {
    showAlert(message, 'danger');
}

// Generic alert function
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid main') || document.body;
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        const alert = container.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Debounce function for search inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = function() {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search functionality
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    const debouncedSearch = debounce(function(query) {
        // Implement search logic here
        console.log('Searching for:', query);
    }, 300);
    
    searchInput.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
}

// Export functionality
function exportData(format) {
    const exportBtn = document.querySelector('[data-export]');
    if (exportBtn) {
        const hideLoading = showLoading(exportBtn);
        
        // Simulate export process
        setTimeout(function() {
            hideLoading();
            showSuccess(`Data exported successfully as ${format.toUpperCase()}`);
        }, 2000);
    }
}

// Theme toggle (if implemented)
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('preferred-theme', newTheme);
}

// Load saved theme
function loadTheme() {
    const savedTheme = localStorage.getItem('preferred-theme');
    if (savedTheme) {
        document.body.setAttribute('data-theme', savedTheme);
    }
}

// Initialize theme on page load
loadTheme();

// Questionnaire functionality
function startQuestionnaire(type) {
    window.location.href = `questionnaire.php?type=${type}&action=start`;
}

// Handle questionnaire navigation
function nextQuestion() {
    // Implementation for questionnaire navigation
    console.log('Next question');
}

function previousQuestion() {
    // Implementation for questionnaire navigation
    console.log('Previous question');
}

// Progress tracking
function updateProgress(current, total) {
    const progressBar = document.querySelector('.questionnaire-progress .progress-bar');
    if (progressBar) {
        const percentage = (current / total) * 100;
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', current);
        progressBar.textContent = `${current}/${total}`;
    }
}

// AI Recommendation simulation
function getAIRecommendations() {
    const recommendations = [
        "Fokus pada materi Matematika untuk meningkatkan skor",
        "Selesaikan quiz Bahasa Inggris yang tertunda",
        "Review materi Fisika berdasarkan performa terakhir",
        "Ikuti kuesioner MSLQ untuk analisis pembelajaran",
        "Bergabung dengan diskusi grup untuk materi sulit"
    ];
    
    return recommendations[Math.floor(Math.random() * recommendations.length)];
}

// Display AI recommendations
function showAIRecommendations() {
    const recommendationElement = document.getElementById('aiRecommendations');
    if (recommendationElement) {
        const recommendation = getAIRecommendations();
        recommendationElement.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-robot me-2"></i>
                <strong>AI Recommendation:</strong> ${recommendation}
            </div>
        `;
    }
}

// Initialize AI recommendations
setTimeout(showAIRecommendations, 3000);
