<?
/** @var \Kitrix\Plugins\Plugin[] $plugins */
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
        "id" => "ENABLED",
        "content" => "Включен?",
        "default" => true,
    ],
    [
        "id" => "REQUIREMENTS",
        "content" => "Зависимости",
        "default" => true,
    ]
]);

foreach ($plugins as $plugin) {

    $row =& $adminTable->AddRow($plugin->getHash(), []);

    // ============ NAME =============

    ob_start();
    ?>
    <b>
        <i class="fa <?=$plugin->useIcon()?>"></i>
        <?=$plugin->useAlias()?> (<?=$plugin->getId()?>)
    </b><br/>
    <small>
        <?=$plugin->getConfDesc()?>
        <br /><br /><i>Лицензия: <?=$plugin->getConfLicence()?></i>
    </small>
    <?
    $_fName = ob_get_clean();

    // ============ AUTHORS =============
    ob_start();
    ?>
    <?foreach ($plugin->getConfAuthors() as $fields):?>

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
    <?foreach ($plugin->getDependencies() as $name => $version):?>
        <div>
            <a href="https://packagist.org/packages/<?=$name?>" target="_blank"><?=$name?></a> (<?=$version?>)
        </div>
    <?endforeach;?>
    <?
    $_fRequirements = ob_get_clean();

    // ============ PROVIDE TEMPLATES TO TABLE =============

    $row->AddViewField("NAME", $_fName);
    $row->AddViewField("AUTHOR", $_fAuthors);
    $row->AddViewField("ENABLED", $_fStatus);
    $row->AddViewField("REQUIREMENTS", $_fRequirements);

    // ============ ACTIONS =============

    $row->AddActions([
        [
            "ICON" => "add",
            "DEFAULT" => true,
            "TEXT" => "Включить",
            "ACTION" => "console.log('test')",
        ],
    ]);
}

$adminTable->CheckListMode();
?>

<?$adminTable->DisplayList();?>