<?php
// /includes/faq.php
// 保证核心和 PDO 存在
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/header.php';

$faqs = [];

try {
    $stmt = $pdo->prepare("
        SELECT id, question, answer
        FROM faqs
        WHERE is_active = 1
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute();
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $faqs = [];
}
?>

<!-- Faq Section -->
<section id="faq" class="faq section light-background">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Frequently Asked Questions</h2>
    <p>Here we provide clear answers to the questions buyers ask most often about our products, service, and manufacturing process.</p>
  </div><!-- End Section Title -->

  <div class="container">

    <div class="row justify-content-center">
      <div class="col-lg-8">

        <div class="faq-container">

          <?php if (empty($faqs)): ?>

            <div class="faq-item faq-active" data-aos="fade-up" data-aos-delay="200">
              <i class="faq-icon bi bi-question-circle"></i>
              <h3>No FAQ content yet</h3>
              <div class="faq-content">
                <p>
                  You can add FAQ items in database table
                  <code>faqs</code> (is_active = 1).
                </p>
              </div>
              <i class="faq-toggle bi bi-chevron-right"></i>
            </div>

          <?php else: ?>
            <?php
              $index = 0;
              foreach ($faqs as $item):
                $index++;
                $item_class = 'faq-item';
                if ($index === 1) {
                  $item_class .= ' faq-active';
                }
                $delay = 100 + $index * 100;
            ?>
              <div class="<?= $item_class ?>" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                <i class="faq-icon bi bi-question-circle"></i>
                <h3><?= htmlspecialchars($item['question']) ?></h3>
                <div class="faq-content">
                  <p><?= nl2br(htmlspecialchars($item['answer'])) ?></p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->
            <?php endforeach; ?>
          <?php endif; ?>

        </div><!-- /.faq-container -->

      </div>
    </div>

  </div>

</section><!-- /Faq Section -->

<?php require_once __DIR__ . '/footer.php'; ?>
