<?
/** @var string $content */
/** @var \Kitrix\Plugins\PluginMeta $plugin */
?>

<?if($content):?>

    <h1>
        <i class="fa <?=$plugin->getConfig()->getIcon()?>"></i>
        <?=$plugin->getConfig()->getAlias()?> - Документация:
    </h1>
    <div class="ktrx-doc-block">
        <?=$content?>
    </div>

    <!-- code highlight tool -->
    <style rel="stylesheet">
        .hljs{display:block;overflow-x:auto;padding:0.5em;color:#383a42;background:#fafafa}.hljs-comment,.hljs-quote{color:#a0a1a7;font-style:italic}.hljs-doctag,.hljs-keyword,.hljs-formula{color:#a626a4}.hljs-section,.hljs-name,.hljs-selector-tag,.hljs-deletion,.hljs-subst{color:#e45649}.hljs-literal{color:#0184bb}.hljs-string,.hljs-regexp,.hljs-addition,.hljs-attribute,.hljs-meta-string{color:#50a14f}.hljs-built_in,.hljs-class .hljs-title{color:#c18401}.hljs-attr,.hljs-variable,.hljs-template-variable,.hljs-type,.hljs-selector-class,.hljs-selector-attr,.hljs-selector-pseudo,.hljs-number{color:#986801}.hljs-symbol,.hljs-bullet,.hljs-link,.hljs-meta,.hljs-selector-id,.hljs-title{color:#4078f2}.hljs-emphasis{font-style:italic}.hljs-strong{font-weight:bold}.hljs-link{text-decoration:underline}
    </style>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.9.0/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script>

<?else:?>
    Документация к плагину не найдена.
<?endif;?>
