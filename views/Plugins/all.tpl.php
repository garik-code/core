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
        "id" => "INSTALLED",
        "content" => "Установлен?",
        "default" => true,
    ],
    [
        "id" => "ENABLED",
        "content" => "Включен?",
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
            <small style="color: #1952D3;">
                <i class="fa fa-lock"></i>
                Защишен
            </small>
        <?endif;?>
    </b><br/>
    <small>
        <?=$plugin->getConfig()->getDesc()?>
        <br /><br /><i>Лицензия: <?=$plugin->getConfig()->getLicence()?></i>
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

    // ============ INSTALLED? =============
    ob_start();
    ?>
    <div class="ktrx-core-plugin-status <?=$plugin->isInstalled() ? "ktrx-core-enabled" : "ktrx-core-disabled"?>">
        <?if($plugin->isInstalled()):?>
            <span class="fa fa-check"></span>
            Установлен
        <?else:?>
            <span class="fa fa-exclamation-circle"></span>
            Не установлен
        <?endif;?>
    </div>
    <?
    $_fInstalled = ob_get_clean();

    // ============ ENABLED? =============
    ob_start();
    ?>
        <div class="ktrx-core-plugin-status <?=!$plugin->isDisabled() ? "ktrx-core-enabled" : "ktrx-core-disabled"?>">
            <div class="kitrix-core-status-circle"></div>
            <?=!$plugin->isDisabled() ? "Включен" : "Выключен"?>
        </div>
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
    $row->AddViewField("INSTALLED", $_fInstalled);
    $row->AddViewField("ENABLED", $_fStatus);
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
