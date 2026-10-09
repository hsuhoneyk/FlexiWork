<!-- ============ COOKIE CONSENT BANNER (always shows) ============ -->
<!-- Paste this block right before </body> in every page of your site. -->

<div id="cookie-banner" class="cookie-banner" role="dialog" aria-live="polite" aria-label="Cookie consent">
    <div class="cookie-banner__text">
        <p>
            We use cookies to improve your experience and, if you allow it,
            to measure site usage with analytics. See our
            <a href="privacy.php">Privacy Policy</a> for details.
        </p>
    </div>
    <div class="cookie-banner__actions">
        <button id="cookie-decline" class="cookie-btn cookie-btn--ghost" type="button">Decline</button>
        <button id="cookie-accept" class="cookie-btn cookie-btn--primary" type="button">Accept</button>
    </div>
</div>

<script>
    (function() {
        // NOTE: no localStorage check here — the banner shows on every
        // single page load / every visit, with no "remember my choice".
        var banner = document.getElementById('cookie-banner');
        var acceptBtn = document.getElementById('cookie-accept');
        var declineBtn = document.getElementById('cookie-decline');

        function hideBanner() {
            banner.classList.add('is-hidden');
        }

        // Activates any script tag written as type="text/plain" data-cookie-category="analytics"
        // — this is what keeps GA/GTM blocked until the visitor clicks Accept on THIS page view.
        function loadConsentedScripts() {
            var blocked = document.querySelectorAll('script[type="text/plain"][data-cookie-category="analytics"]');
            blocked.forEach(function(oldScript) {
                var newScript = document.createElement('script');
                for (var i = 0; i < oldScript.attributes.length; i++) {
                    var attr = oldScript.attributes[i];
                    if (attr.name !== 'type') newScript.setAttribute(attr.name, attr.value);
                }
                newScript.text = oldScript.text;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }

        acceptBtn.addEventListener('click', function() {
            hideBanner();
            loadConsentedScripts();
        });

        declineBtn.addEventListener('click', function() {
            hideBanner();
            // Analytics scripts stay blocked for this page view.
        });
    })();
</script>
<!-- ========== END COOKIE CONSENT BANNER (always shows) ========== -->

<!--
  BLOCKING GOOGLE ANALYTICS / GTM (same as before):
  Change type="text/javascript" to type="text/plain" and add
  data-cookie-category="analytics" on your GA/GTM script tag(s).
  They'll only activate after "Accept" is clicked on that page view.
-->