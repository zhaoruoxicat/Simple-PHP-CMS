<?php
// /includes/footer.php
require_once __DIR__ . '/../core.php';

$site_name     = get_setting('company_name', 'Your Company');
$company_addr  = get_setting('company_address', 'Your company address here');
$company_phone = get_setting('company_phone', '+00 0000 0000');
$company_email = get_setting('company_email', 'info@example.com');
$footer_copy   = get_setting('footer_copyright', '');

// 从 cms_settings 读取 WhatsApp / WeChat
$company_whatsapp = get_setting('company_whatsapp', '');
$company_wechat   = get_setting('company_wechat', '');

// ===== 社交媒体链接 =====
$linkX         = trim(get_setting('social_x', ''));
$linkFacebook  = trim(get_setting('social_facebook', ''));
$linkInstagram = trim(get_setting('social_instagram', ''));
$linkLinkedin  = trim(get_setting('social_linkedin', ''));

if (!function_exists('render_social_icon')) {
    function render_social_icon($url, $iconClass, $extraClass = '') {
        $url = trim((string)$url);
        if ($url === '') return '';
        return '<a href="' . htmlspecialchars($url) . '" class="' . htmlspecialchars($extraClass) . '" target="_blank" rel="noopener">'
             . '<i class="bi ' . htmlspecialchars($iconClass) . '"></i>'
             . '</a>';
    }
}
?>

  </main><!-- End #main -->

  <!-- Footer -->
  <footer id="footer" class="footer light-background">

    <div class="container footer-top">
      <div class="row gy-4">

        <!-- 公司信息 -->
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="/index.html" class="logo d-flex align-items-center">
            <span class="sitename"><?= htmlspecialchars($site_name) ?></span>
          </a>
          <div class="footer-contact pt-3">
            <?php if ($company_addr !== ''): ?>
              <p><?= nl2br(htmlspecialchars($company_addr)) ?></p>
            <?php endif; ?>

            <?php if ($company_phone !== ''): ?>
              <p class="mt-3">
                <strong>Phone:</strong>
                <span><?= htmlspecialchars($company_phone) ?></span>
              </p>
            <?php endif; ?>

            <?php if ($company_email !== ''): ?>
              <p>
                <strong>Email:</strong>
                <span><?= htmlspecialchars($company_email) ?></span>
              </p>
            <?php endif; ?>

            <?php if ($company_whatsapp !== ''): ?>
              <p>
                <strong>WhatsApp:</strong>
                <span><?= htmlspecialchars($company_whatsapp) ?></span>
              </p>
            <?php endif; ?>

            <?php if ($company_wechat !== ''): ?>
              <p>
                <strong>WeChat:</strong>
                <span><?= htmlspecialchars($company_wechat) ?></span>
              </p>
            <?php endif; ?>
          </div>

          <div class="social-links d-flex mt-4">
            <?= render_social_icon($linkX,         'bi-twitter-x',  'twitter') ?>
            <?= render_social_icon($linkFacebook,  'bi-facebook',   'facebook') ?>
            <?= render_social_icon($linkInstagram, 'bi-instagram',  'instagram') ?>
            <?= render_social_icon($linkLinkedin,  'bi-linkedin',   'linkedin') ?>
          </div>
        </div>

        <!-- 快速链接 -->
        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Links</h4>
          <ul>
            <li><a href="/index.html">Home</a></li>
            <li><a href="/products_list/index.html">Products</a></li>
            <li><a href="/about.html">About</a></li>
            <li><a href="/faq.html">FAQ</a></li>
            <li><a href="/contact.html">Contact</a></li>
          </ul>
        </div>

        <!-- 页脚留言表单 -->
        <div class="col-lg-4 col-md-6 footer-newsletter">
          <h4>Leave Us a Message</h4>
          <p>Send us your inquiry or feedback, we will reply as soon as possible.</p>

          <!-- ✅ 静态页提示容器：由 JS 根据 URL 参数显示 -->
          <div id="footerMsgBox" style="display:none;" class="alert py-2 px-3 mt-2 mb-2"></div>

          <!-- ✅ 关键：永远提交到 PHP 处理器，而不是当前页面（静态 cache html 也能提交） -->
          <form action="/footer_message_submit.php" method="post" id="footerMessageForm">
            <input type="hidden" name="footer_message_form" value="1">

            <!-- 返回地址：让处理器写库后跳回用户当前访问的页面（可能是 /cache/*.html 触发的 /xxx.html） -->
            <input type="hidden" name="return_url" id="footer_return_url" value="">

            <!-- 时区 -->
            <input type="hidden" name="timezone" id="footer_timezone" value="">

            <div class="mb-2">
              <input type="email"
                     name="email"
                     class="form-control"
                     placeholder="Your email"
                     autocomplete="email"
                     required>
            </div>

            <div class="mb-2">
              <textarea name="message"
                        class="form-control"
                        rows="3"
                        placeholder="Your message (optional)"></textarea>
            </div>

            <div>
              <button type="submit" class="btn btn-primary btn-sm">Send Message</button>
            </div>
          </form>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <?php if ($footer_copy !== ''): ?>
        <p><?= htmlspecialchars($footer_copy) ?></p>
      <?php else: ?>
        <p>© <span><?= date('Y') ?></span>
          <strong class="px-1 sitename"><?= htmlspecialchars($site_name) ?></strong>
          <span>All Rights Reserved</span></p>
      <?php endif; ?>
    </div>

  </footer><!-- End Footer -->

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="/includes/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="/includes/assets/vendor/php-email-form/validate.js"></script>
  <script src="/includes/assets/vendor/aos/aos.js"></script>
  <script src="/includes/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="/includes/assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="/includes/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="/includes/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="/includes/assets/js/main.js"></script>

  <!-- ✅ 留言：写入时区 + 写入 return_url + 静态页提示显示 -->
  <script>
    (function () {
      // 1) 写入用户时区
      try {
        var tzInput = document.getElementById('footer_timezone');
        if (tzInput && window.Intl && Intl.DateTimeFormat) {
          var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
          if (tz) tzInput.value = tz;
        }
      } catch (e) {}

      // 2) return_url：用于提交后跳回当前页面（包括 html 访问形态）
      try {
        var returnInput = document.getElementById('footer_return_url');
        if (returnInput) {
          returnInput.value = window.location.pathname + window.location.search + window.location.hash;
        }
      } catch (e) {}

      // 3) 根据 URL 参数显示提示（静态页也能显示）
      try {
        var params = new URLSearchParams(window.location.search);
        var state = params.get('footer_msg'); // ok | err
        if (state) {
          var box = document.getElementById('footerMsgBox');
          if (box) {
            if (state === 'ok') {
              box.className = 'alert alert-success py-2 px-3 mt-2 mb-2';
              box.textContent = 'Your message has been received. Thank you!';
            } else {
              box.className = 'alert alert-danger py-2 px-3 mt-2 mb-2';
              box.textContent = 'Failed to submit, please try again later.';
            }
            box.style.display = '';
            // 滚动到 footer
            var footer = document.getElementById('footer');
            if (footer && footer.scrollIntoView) footer.scrollIntoView({behavior:'smooth', block:'end'});
          }
        }
      } catch (e) {}
    })();
  </script>

</body>
</html>
