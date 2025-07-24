<?php
// Data for this view will be passed from the VarkCorrelationAnalysisController
$user = $user ?? ['name' => 'Guest'];
$vark_data = $vark_data ?? [];
$dominant_style = $dominant_style ?? 'N/A';
?>

<div class="hero-section text-center">
    <h1><i class="fas fa-chart-network me-3"></i>VARK-MSLQ-AMS Correlation Analysis</h1>
    <p class="lead">Understanding the relationship between learning style, motivation, and learning strategies</p>
    <p>Welcome, <strong><?php echo htmlspecialchars($user['name']); ?></strong>!</p>
</div>

<!-- Status Check -->
<div class="alert alert-info">
    <h6><i class="fas fa-info-circle me-2"></i>System Status</h6>
    <p class="mb-1">✅ PHP Session: Active</p>
    <p class="mb-1">✅ Bootstrap CSS: Loaded</p>
    <p class="mb-0">✅ FontAwesome Icons: Loaded</p>
</div>

<!-- VARK Scores Display -->
<div class="correlation-card">
    <h4><i class="fas fa-eye me-2"></i>VARK Scores (Demo Data)</h4>
    <div class="row mt-3">
        <?php foreach ($vark_data as $style => $score): ?>
        <div class="col-md-3 text-center mb-3">
            <div class="border rounded p-3 <?php echo $style === $dominant_style ? 'border-primary bg-light' : ''; ?>">
                <h6><?php echo $style; ?></h6>
                <div class="score-display <?php echo $style === $dominant_style ? 'text-primary' : 'text-muted'; ?>">
                    <?php echo $score; ?>
                </div>
                <?php if ($style === $dominant_style): ?>
                    <small class="text-primary"><strong>Dominant</strong></small>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-3">
        <span class="style-badge bg-primary text-white">
            Dominant Learning Style: <?php echo $dominant_style; ?>
        </span>
    </div>
</div>

<!-- Correlation Predictions -->
<div class="row">
    <div class="col-md-6">
        <div class="correlation-card">
            <h5><i class="fas fa-brain me-2 text-primary"></i>MSLQ Correlation Predictions</h5>
            <p class="text-muted">For <?php echo $dominant_style; ?> Learners:</p>
            
            <?php if ($dominant_style === 'Reading/Writing'): ?>
            <ul>
                <li><strong>Elaboration</strong> - High Correlation (r ≈ 0.80)</li>
                <li><strong>Metacognitive Self-Regulation</strong> - Very High (r ≈ 0.75)</li>
                <li><strong>Organization</strong> - High (r ≈ 0.72)</li>
            </ul>
            <div class="alert alert-primary">
                <strong>Insight:</strong> Excellent at written elaboration and self-monitoring through writing.
            </div>
            <?php else: ?>
            <ul>
                <li><strong>Various strategies</strong> - See documentation for specific correlations</li>
                <li><strong>Learning preferences</strong> - Based on <?php echo $dominant_style; ?> style</li>
            </ul>
            <div class="alert alert-info">
                <strong>Note:</strong> Correlation patterns vary by learning style.
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="correlation-card">
            <h5><i class="fas fa-heart me-2 text-success"></i>AMS Correlation Predictions</h5>
            <p class="text-muted">Motivation for <?php echo $dominant_style; ?> Learners:</p>
            
            <?php if ($dominant_style === 'Reading/Writing'): ?>
            <ul>
                <li><strong>Intrinsic - To Know</strong> - Very High (r ≈ 0.78)</li>
                <li><strong>Intrinsic - To Accomplish</strong> - High (r ≈ 0.70)</li>
                <li><strong>External - Identified</strong> - Moderate (r ≈ 0.48)</li>
            </ul>
            <div class="alert alert-success">
                <strong>Insight:</strong> Highest motivation for knowledge acquisition through reading.
            </div>
            <?php else: ?>
            <ul>
                <li><strong>Intrinsic motivation</strong> - Generally high for this style</li>
                <li><strong>External motivation</strong> - Varies by individual</li>
            </ul>
            <div class="alert alert-info">
                <strong>Note:</strong> Motivation patterns depend on learning style preferences.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recommendations -->
<div class="correlation-card">
    <h5><i class="fas fa-lightbulb me-2 text-warning"></i>Personalized Recommendations</h5>
    <div class="row">
        <div class="col-md-6">
            <h6>🎯 Optimal Learning Strategies:</h6>
            <?php if ($dominant_style === 'Reading/Writing'): ?>
            <ul>
                <li>Maximize elaboration and metacognitive strategies</li>
                <li>Focus on independent learning and self-regulation</li>
                <li>Use journaling and note-taking extensively</li>
                <li>Utilize written materials and text-based resources</li>
            </ul>
            <?php else: ?>
            <ul>
                <li>Adapt strategies based on <?php echo $dominant_style; ?> preferences</li>
                <li>Use multi-modal learning approaches</li>
                <li>Leverage strengths of your dominant style</li>
                <li>Refer to documentation for detailed recommendations</li>
            </ul>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h6>💪 Motivation Enhancement:</h6>
            <?php if ($dominant_style === 'Reading/Writing'): ?>
            <ul>
                <li>Leverage intrinsic motivation to know</li>
                <li>Set achievement goals through written work</li>
                <li>Provide comprehensive reading materials</li>
                <li>Focus on knowledge acquisition activities</li>
            </ul>
            <?php else: ?>
            <ul>
                <li>Enhance intrinsic motivation through preferred modalities</li>
                <li>Create engaging activities matching your style</li>
                <li>Build on natural learning preferences</li>
                <li>See style-specific recommendations in documentation</li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="text-center my-4">
    <a href="/questionnaire-progress" class="btn btn-primary me-2">
        <i class="fas fa-arrow-left me-1"></i>Back to Questionnaire Progress
    </a>
    <a href="/dashboard" class="btn btn-secondary me-2">
        <i class="fas fa-home me-1"></i>Dashboard
    </a>
    <a href="#" class="btn btn-info" onclick="alert('Documentation feature coming soon!')">
        <i class="fas fa-book me-1"></i>Documentation
    </a>
</div>