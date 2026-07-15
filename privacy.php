<?php
require_once __DIR__ . '/config/functions.php';
$page_title       = 'Privacy Policy | ' . setting('site_name');
$page_description = 'Privacy Policy for ' . setting('site_name') . '.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container legal-page" style="max-width:880px">
    <h1 class="section-title mb-4">Privacy Policy</h1>

    <p>This Privacy Policy describes how <?= e(setting('site_name')) ?> (&ldquo;we&rdquo;,
    &ldquo;us&rdquo; or &ldquo;our&rdquo;) collects, uses, stores and protects the personal
    information of users (&ldquo;you&rdquo; or &ldquo;your&rdquo;) of this website
    (the &ldquo;Platform&rdquo;). By accessing or using the Platform, you agree to the collection
    and use of your information in accordance with this Privacy Policy. If you do not agree with
    this policy, please do not use the Platform.</p>

    <h4 class="mt-4">Information We Collect</h4>
    <p>We collect the following information when you register for our programs, make a purchase, or
    contact us through the Platform:</p>
    <ul>
      <li><strong>Contact information</strong> — such as your name, email address, phone / WhatsApp
      number, city and state.</li>
      <li><strong>Registration details</strong> — such as your child&apos;s name, grade / class,
      syllabus and any questions or expectations you share with us.</li>
      <li><strong>Transaction information</strong> — details of the products and/or services you
      purchase, the amount paid and payment references provided by our payment gateway. We do
      <strong>not</strong> collect or store your card numbers, UPI PINs, bank account credentials or
      any other sensitive payment instrument details; these are handled directly and securely by our
      payment gateway partner (Instamojo).</li>
      <li><strong>Technical information</strong> — such as browser type, device information and pages
      visited, collected automatically to keep the Platform secure and improve your experience.</li>
    </ul>

    <h4 class="mt-4">How We Use Your Information</h4>
    <ul>
      <li>To provide and deliver the products and/or services you request, including webinars,
      mentorship and workshops.</li>
      <li>To process your payments and confirm your transactions.</li>
      <li>To communicate with you about your registrations, purchases, schedules and updates,
      including via email, phone and WhatsApp.</li>
      <li>To respond to your questions, requests and grievances.</li>
      <li>To improve the Platform, our offerings and customer experience.</li>
      <li>To comply with applicable laws, regulations and legal processes.</li>
    </ul>

    <h4 class="mt-4">Sharing of Information</h4>
    <p>We do not sell, rent or trade your personal information to third parties. We may share your
    information only in the following circumstances:</p>
    <ul>
      <li>With our payment gateway partner (Instamojo) to the extent necessary to process your
      payments, prevent fraud and comply with their verification requirements.</li>
      <li>With service providers who assist us in operating the Platform (such as hosting providers),
      who are bound to keep your information confidential.</li>
      <li>Where required by law, regulation, court order or governmental authority, or to protect our
      rights, safety or property.</li>
    </ul>

    <h4 class="mt-4">Cookies</h4>
    <p>The Platform uses cookies and similar technologies that are strictly necessary for its
    operation — for example, session cookies that keep forms secure. You can control or delete
    cookies through your browser settings; disabling them may affect certain features of the
    Platform.</p>

    <h4 class="mt-4">Data Security</h4>
    <p>We adopt reasonable security practices and procedures to protect your personal information
    from unauthorized access, alteration, disclosure or destruction. All payment transactions are
    processed over secure, encrypted connections by our payment gateway partner. However, no method
    of transmission over the internet is completely secure, and we cannot guarantee absolute
    security.</p>

    <h4 class="mt-4">Data Retention</h4>
    <p>We retain your personal information only for as long as necessary to fulfil the purposes
    described in this policy, to provide our services to you, and to comply with our legal and
    accounting obligations.</p>

    <h4 class="mt-4">Your Rights</h4>
    <p>You may request access to, correction of, or deletion of the personal information we hold
    about you by writing to us at the contact details below. We will respond to such requests within
    a reasonable time, subject to any legal obligations that require us to retain certain
    information.</p>

    <h4 class="mt-4">Children&apos;s Privacy</h4>
    <p>Our programs may involve information about children (such as a child&apos;s name and grade)
    that is provided to us by their parent or legal guardian. We collect such information only from
    the parent or guardian and use it solely to deliver the requested programs.</p>

    <h4 class="mt-4">Third-Party Links</h4>
    <p>The Platform may contain links to third-party websites (such as social media pages or the
    payment gateway). We are not responsible for the privacy practices or content of those websites,
    and we encourage you to review their privacy policies.</p>

    <h4 class="mt-4">Changes to this Policy</h4>
    <p>We may update this Privacy Policy from time to time by posting the revised version on this
    page. You are advised to review this page periodically. Continued use of the Platform after
    changes are posted constitutes your acceptance of the revised policy.</p>

    <h4 class="mt-4">Grievance Redressal &amp; Contact</h4>
    <p>If you have any questions, concerns or grievances regarding this Privacy Policy or the
    handling of your personal information, please contact us:</p>
    <ul>
      <li>Email: <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></li>
      <li>Phone: <a href="tel:<?= e(setting('contact_phone')) ?>"><?= e(setting('contact_phone')) ?></a></li>
      <li>Address: <?= e(setting('contact_address')) ?></li>
    </ul>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
