<?php
/*
 * @var array $product
 */
?>

<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="card card-hover h-100">
        <div class="position-relative">
            <img
                src="/public/images/product_placeholder.png"
                class="card-img-top"
                alt=<?= htmlspecialchars($product["name"]) ?>
            />
        </div>
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="card-title"><?= htmlspecialchars($product["name"]) ?></h6>
                <span class="badge bg-light text-dark small">
                    <?= htmlspecialchars($product["category_name"]) ?>
                </span>
            </div>
            <p class="card-text text-muted small flex-grow-1">
                <?= htmlspecialchars($product["description"]) ?>
            </p>
            <div class="d-flex justify-content-between align-items-center mt-auto">
                <div class="d-flex align-items-center">
                    <i class="bi bi-coin text-warning me-1"></i>
                    <strong class="text-primary">
                        <?= htmlspecialchars($product["points_price"]) ?>
                    </strong>
                </div>
                <button class="btn btn-sm btn-primary">
                    Redeem
                </button>
            </div>
        </div>
    </div>
</div>

