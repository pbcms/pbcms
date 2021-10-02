<?php
    use Library\Policy;

    $policy = new Policy;

    $limit = 50;
    $offset = 0;
    if (isset($_GET['limit'])) $limit = (intval($_GET['limit']) < 499 ? intval($_GET['limit']) : 500);
    if (isset($_GET['offset'])) $offset = intval($_GET['offset']);

    $tableContent = '';
    $policies = $policy->list($limit, $offset);
    foreach($policies as $item) {
        $item = (object) $item;
        switch($item->name) {
            case 'site-indexing':
            case 'usernames-enabled':
            case 'usernames-required':
            case 'signup-allowed':
            case 'profilepicture-allowed':
            case 'user-email-verification':
            case 'allow-stay-signedin':
            case 'pbcms-safe-mode':
            case 'show-welcome-page':
            case 'site-email-public':
                $item->type = 'toggle';
                break;
            case 'usernames-minimum-length':
            case 'usernames-maximum-length':
            case 'session-default-expiration':
            case 'token-default-expiration':
            case 'access-token-expiration':
                $item->type = 'number';
                break;
            default:
                $item->type = 'string';
        }

        $tableContent .= renderRow($item);
    }

    function renderRow($item) {
        $result = '<tr policy-name="' . $item->name . '"><th class="column-id">' . $item->id . '</th><td id="policy-name">' . $item->name . '</td><td class="no-padding">';
        switch($item->type) {
            case 'string':
                $result .= '<input type="text" name="' . $item->name . '" value="' . $item->value . '" placeholder="Enter a value">';
                break;
            case 'number':
                $result .= '<input type="number" name="' . $item->name . '" value="' . $item->value . '" placeholder="Enter a value">';
                break;
            case 'toggle':
                $result .= '<div class="input-toggle"><input type="checkbox" id="pbd-pol-' . $item->name . '" name="' . $item->name . '" ' . (intval($item->value) == 1 ? "checked" : "") . '><label for="pbd-pol-' . $item->name . '"></label></div>';
                break;
        }

        $result .= "</td></tr>";
        return $result;
    }
?>

<section class="page-introduction">
    <h1>
        <?php echo $this->lang->get("pages.pb-dashboard.policies.page-title", "Policy editor"); ?>
    </h1>
    <p>
        <b><?php echo ucfirst($this->lang->get("common.words.warning", "Warning")); ?>:</b> <?php echo $this->lang->get("pages.pb-dashboard.policies.page-subtitle", "Edit policies on your own risk. Editing policies can greatly damage your website."); ?>
    </p>
</section>

<section class="no-padding policy-list">
    <table>
        <thead>
            <th class="column-id">
                <?php echo $this->lang->get("pages.pb-dashboard.policies.table.column-id", "ID"); ?>
            </th>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.policies.table.column-name", "Name"); ?>
            </th>
            <th>
                <?php echo $this->lang->get("pages.pb-dashboard.policies.table.column-value", "Value (editable)"); ?>
            </th>
        </thead>
        <tbody>
            <?php
                echo $tableContent;
            ?>
        </tbody>
    </table>
</section>

<section class="transparent no-padding buttons">
    <a href="#" class="button update-policies light-blue">
        <?php echo ucfirst($this->lang->get("common.words.save", "Save")); ?>
    </a>
    <p class="error"></p>
</section>