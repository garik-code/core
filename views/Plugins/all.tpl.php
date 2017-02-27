<?
/** @var \Kitrix\Plugins\PluginMeta[] $plugins */
/** @var \CAdminList $adminTable */

$adminTable->BeginPrologContent();
$adminTable->AddHeaders([
    [
        "id" => "NAME",
        "content" => "Название плагина",
        "default" => true,
    ],
    [
        "id" => "AUTHOR",
        "content" => "Автор",
        "default" => true,
    ],
    [
        "id" => "STATUS",
        "content" => "Статус",
        "default" => true,
    ],
    [
        "id" => "REQUIREMENTS",
        "content" => "Зависимости",
        "default" => true,
    ],
    [
        "id" => "VERSION",
        "content" => "Версия",
        "default" => true,
    ]
]);

foreach ($plugins as $plugin) {

    $row =& $adminTable->AddRow($plugin->getPid(), []);

    // ============ NAME =============

    ob_start();
    ?>
    <b>
        <i class="fa <?=$plugin->getConfig()->getIcon()?>"></i>
        <?=$plugin->getConfig()->getAlias()?> (<?=$plugin->getPid()?>)
        <?if($plugin->isProtected()):?>
            <small class="ktrx-core-badge ktrx-core-badge-protected" title="Плагин нельзя выключить">
                <i class="fa fa-lock"></i>
                Защишен
            </small>
        <?endif;?>
        <?if($plugin->isLocalSource()):?>
            <small class="ktrx-core-badge ktrx-core-badge-local" title="Локальный плагин, редактируемый">
                <i class="fa fa-pencil"></i>
                local
            </small>
        <?else:?>
            <small class="ktrx-core-badge ktrx-core-badge-vendor" title="Из пакетного менеджера, не редактируемый">
                <i class="fa fa-shield"></i>
                composer
            </small>
        <?endif;?>
    </b><br/>
    <small>
        <?=$plugin->getConfig()->getDesc()?>
        <br /><br /><i>Лицензия: <?=$plugin->getConfig()->getLicence()?></i>
        <?if($plugin->getReadmeMarkdownText() !== ""):?>
            <?
            $documentationUrl = \Kitrix\MVC\Router::getInstance()
                ->generateLinkTo('kitrix_core_plugins_help_{id}', [
                    'id' => $plugin->getUnderscoredName()
                ])
            ?>
            <br /><br />
            <a href="<?=$documentationUrl?>" class="ktrx-core-read-doc">
                <i class="fa fa-book"></i>
                Читать документацию..
            </a>
        <?endif;?>
    </small>
    <?
    $_fName = ob_get_clean();

    // ============ AUTHORS =============
    ob_start();
    ?>
    <?foreach ($plugin->getConfig()->getAuthors() as $fields):?>

        <div>
            <?if(count($fields) >= 1):?>
                    <?foreach ($fields as $fieldName => $field):?>
                        <div><i><?=$fieldName?></i>: <?=$field?></div>
                    <?endforeach;?>
            <?endif;?>
        </div>

    <?endforeach;?>
    <?
    $_fAuthors = ob_get_clean();

    // ============ STATUS =============
    ob_start();
    ?>
    <?if(!$plugin->isDisabled()):?>
        <div class="ktrx-core-plugin-status ktrx-core-enabled">
            <i class="fa fa-check"></i> Работает
        </div>
    <?else:?>
        <?if($plugin->isInstalled()):?>
            <div class="ktrx-core-plugin-status ktrx-core-disabled">
                <div class="kitrix-core-status-circle"></div> Выключен
            </div>
        <?else:?>
            <div class="ktrx-core-plugin-status ktrx-core-not-installed">
                <i class="fa fa-exclamation-circle"></i>
                Не установлен
            </div>
        <?endif;?>
    <?endif;?>

    <?
    $_fStatus = ob_get_clean();

    // ============ REQUIREMENTS =============
    ob_start();
    ?>
    <?
        $depsStatus = $plugin->getDependenciesStatus();
    ?>
    <?foreach ($plugin->getConfig()->getDependencies() as $name => $version):?>
        <div>
            <?
                $isEnabled = $depsStatus[$name];
            ?>

            <div class="ktrx-core-dep-status <?=$isEnabled ? "ktrx-core-enabled" : "ktrx-core-disabled"?>">
                <div class="kitrix-core-status-circle"></div>
            </div>

            <a href="https://packagist.org/packages/<?=$name?>" target="_blank">
                <?=$name?>
            </a>
            (<?=$version?>)
        </div>
    <?endforeach;?>
    <?
    $_fRequirements = ob_get_clean();

    // ============ VERSION =============
    ob_start();
    ?>
    <div><?=$plugin->getConfig()->getVersion()?></div>
    <?
    $_fVersion = ob_get_clean();

    // ============ PROVIDE TEMPLATES TO TABLE =============

    $row->AddViewField("NAME", $_fName);
    $row->AddViewField("AUTHOR", $_fAuthors);
    $row->AddViewField("STATUS", $_fStatus);
    $row->AddViewField("REQUIREMENTS", $_fRequirements);
    $row->AddViewField("VERSION", $_fVersion);

    // ============ ACTIONS =============

    $actions = [];
    if ($plugin->isDisabled())
    {
        $actions[] = [
            "ICON" => "enable",
            "DEFAULT" => true,
            "TEXT" => "Включить",
            "ACTION" => "KitrixCorePlugins.enable(\"{$plugin->getPid()}\")",
        ];

        if ($plugin->isInstalled())
        {
            $actions[] = [
                "ICON" => "delete",
                "DEFAULT" => true,
                "TEXT" => "Деинсталировать (только данные)",
                "ACTION" => "KitrixCorePlugins.uninstall(\"{$plugin->getPid()}\")",
            ];
        }
        else
        {
            $actions[] = [
                "ICON" => "install",
                "DEFAULT" => true,
                "TEXT" => "Установить",
                "ACTION" => "KitrixCorePlugins.install(\"{$plugin->getPid()}\")",
            ];
        }
    }
    else
    {
        $actions[] = [
            "ICON" => "disable",
            "DEFAULT" => true,
            "TEXT" => "Выключить",
            "ACTION" => "KitrixCorePlugins.disable(\"{$plugin->getPid()}\")",
        ];
    }

    $row->AddActions($actions);
}

$adminTable->CheckListMode();
?>

<?$adminTable->DisplayList();?>

<?
    $jsPlugs = [];
    foreach ($plugins as $plugin) {
        $jsPlugs[$plugin->getPid()] = [
            'title' => $plugin->getConfig()->getAlias()
        ];
    }

    $router = \Kitrix\MVC\Router::getInstance();

    $jsParams = json_encode([
        'url' => $router->generateLinkTo('kitrix_core_plugins_edit'),
        'plugins' => $jsPlugs,
        'pids' => array_keys($jsPlugs)
    ])
?>

<script type="application/javascript">
    KitrixCorePluginsParams = <?=$jsParams?>;
</script>
