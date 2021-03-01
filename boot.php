<?php
$package = rex_addon::get('locked');
// Add status for locked articles and categories

if (rex::isBackend()) {
    rex_extension::register(['ART_STATUS_TYPES', 'CAT_STATUS_TYPES'], function (rex_extension_point $ep) {
        $subject = $ep->getSubject();
        $subject[] = [$package->i18n('quicknavi_ignoreoffline'), 'rex-offline', 'fa fa-exclamation-triangle'];
        $ep->setSubject($subject);
        return $ep->getSubject();
    });
}

// redirect to not foundArticle if not logged in or preview parameter not set. 

if (rex::isFrontend()) {
    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex_article::getCurrent() instanceof rex_article && rex_request(preview, string, '') == '' && rex_article::getCurrent()->getValue('status') == 2 && !rex_backend_login::hasSession()) {
            rex_redirect(rex_article::getNotfoundArticleId(), rex_clang::getCurrentId());
        }
    }, rex_extension::LATE);
}

if (rex::isBackend()) {
    if (rex_article::getCurrent()->getValue('status') == 2) {
        rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', function (rex_extension_point $ep) {
            $params = $ep->getParams();
            $subject = $ep->getSubject();

            $panel = '<div class="alert alert-info">Sie können folgenden Link teilen: <br><strong>' . rex_yrewrite::getFullUrlByArticleId($params["article_id"]) . '?preview=ok</strong></div>';

            $fragment = new rex_fragment();
            $fragment->setVar('title', '<i class="fa fa-exclamation-triangle" style="color: red"></i> Dieser Artikel ist gesperrt', false);
            $fragment->setVar('body', $panel, false);
            $fragment->setVar('article_id', $params["article_id"], false);

            $fragment->setVar('collapse', true);
            $fragment->setVar('collapsed', false);
            $content = $fragment->parse('core/page/section.php');

            return $subject . $content;
        });
    }
}
