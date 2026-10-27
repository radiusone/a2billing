<?php

namespace A2billing\Forms;

use A2billing\Admin;
use A2billing\Connection;
use A2billing\Forms\Elements\Edit\HasMany;
use A2billing\Forms\Elements\Edit\Input;
use A2billing\Forms\Elements\Edit\Section;
use A2billing\Forms\Elements\Edit\Select;
use A2billing\Forms\Elements\Edit\Textarea;
use A2billing\Forms\Elements\Search;
use A2billing\Forms\Traits\EditMethods;
use A2billing\Forms\Traits\ListMethods;
use A2billing\Forms\Traits\SearchMethods;
use A2billing\Logger;
use DateTime;
use DOMDocument;
use DOMException;
use Illuminate\Database\Query\Builder;
use PhpProfiler\Console;
use Throwable;

/***************************************************************************
 *
 * Class.FormHandler.php : FormHandler - PHP : Handle, Form Generator (FG) for A2Billing
 * Written for PHP 4.x & PHP 5.X versions.
 *
 * A2Billing -- Billing solution for use with Asterisk(tm).
 * Copyright (C) 2004, 2009 Belaid Arezqui <areski _atl_ gmail com>
 *
 * See http://www.a2billing.org for more information about
 * the A2Billing project.
 * Please submit bug reports, patches, etc to <areski _atl_ gmail com>
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 ****************************************************************************/
class FormHandler
{
    use EditMethods;
    use ListMethods;
    use SearchMethods;

    public const CONFIRM_NONE = 0;
    public const CONFIRM_WARN = 1;
    public const CONFIRM_DELETE = 2;

    /** @var FormHandler this is used extensively by the FormBO class */
    private static self $Instance;

    /** @var bool if the current submission has passed all validation checks */
    public bool $all_fields_valid = true;

    /** @var Builder the query builder instance used for DB access */
    public Builder $query_builder;

    /** @var bool|int The result of a non-select query (bool for update and delete, inserted ID for insert) */
    public int|bool $QUERY_RESULT = false;

    /* CONFIG THE VIEWER : CV */
    public ListView $listView;

    /** @var string Message to display if there's no data found for list view */
    public string $list_message_empty = "THERE IS NO RECORD !";

    public string $list_message_intro = '';

    /** @var string[] Parameters to add to the URL of the list view sorting/pagination buttons */
    public array $CV_FOLLOWPARAMETERS = [];

    /** @var string The name of the element you are managing */
    public string $FG_INSTANCE_NAME = "";

    /** The primary key column of the table */
    public string $FG_QUERY_PRIMARY_KEY = 'id';

    /** @var array Primary key of entries that can't be deleted */
    public array $FG_DELETION_FORBIDDEN_ID = [];

    /** @var int Number of pages in the current list view */
    public int $FG_LIST_VIEW_PAGE_COUNT = 0;
    /** @var int Number of rows in the current list view */
    public int $FG_LIST_VIEW_ROW_COUNT = 0;

    /** @var string[] $exportColumns */
    public array $exportColumns = [];

    /** @var bool if false, edit or ask-edit action is not allowed */
    public bool $allowEdits = false;

    /** @var bool if false, delete or ask-delete action is not allowed */
    protected bool $allowDeletes = false;

    /** @var bool if false, add or ask-add action is not allowed */
    protected bool $allowAdds = false;

    /** @var string help text shown in all views (can be overridden for individual views) */
    public string $help_text = "";

    /** @var string help text for list view */
    public string $list_help_text = "";

    /** @var string help text for edit view */
    public string $edit_help_text = "";

    /** @var string help text for add view */
    public string $add_help_text = "";

    /** @var string help text for delete view */
    public string $del_help_text = "";

    //	-------------------- DATA FOR THE EDITION --------------------

    public EditForm $editForm;


    /** @var array columns/values to be used as a condition in update/delete queries as well as fetching for edits */
    public array $update_query_conditions = ["id" => "%id"];

    /**
     * @var array
     * @todo used only in FG_var_signup.inc, should figure a way to get rid of this
     */
    public array $REALTIME_SIP_IAX_INFO = [];

    /** @var string Where to redirect the user after adding a record; expected to end with = and will have ID appended */
    public string $FG_LOCATION_AFTER_ADD;

    /** @var string Where to redirect the user after deleting a record; expected to end with = and will have ID appended */
    public string $FG_LOCATION_AFTER_DELETE;

    /** @var string Where to redirect the user after editing a record; expected to end with = and will have ID appended */
    public string $FG_LOCATION_AFTER_EDIT;

    /** @var string Header text for the add page */
    public string $add_message_intro;

    /** @var string Header text for the edit page */
    public string $edit_message_intro;

    /** @var string Header text for the delete page */
    public string $delete_message_intro;

    /** @var string Result text for the add page */
    public string $add_message_result;

    /** @var string Result text for the delete page */
    public string $delete_message_result;

    /** @var string Error text for the add page */
    public string $add_message_error;

    /** @var string Error text for the edit page */
    public string $edit_message_error;

    /** @var string Error text for the delete page */
    public string $delete_message_error;

    /** @var (callable(int|numeric-string, array<string,mixed>):void)|null Static method of FormBO class executed after creating */
    public $FG_ADDITIONAL_FUNCTION_AFTER_ADD = null;

    /** @var (callable(int|numeric-string):void)|null Static method of FormBO class executed before deleting */
    public $FG_ADDITIONAL_FUNCTION_BEFORE_DELETE = null;

    /** @var (callable(int|numeric-string):void)|null Static method of FormBO class executed after deleting */
    public $FG_ADDITIONAL_FUNCTION_AFTER_DELETE = null;

    /** @var (callable(int|numeric-string, array<string,mixed>):void)|null Static method of FormBO class executed before editing */
    public $FG_ADDITIONAL_FUNCTION_BEFORE_EDIT = null;

    /**
     * Foreign keys indexed by table
     * When set, and matching rows are found, the user may be warned before
     * deleting a child record, depending on $fk_confirm_delete. Behaviour
     * during delete depends on the value of $fk_full_delete
     *
     * @var array<string,string>
     */
    public array $foreign_keys = [];

    /**
     * Delete child records with the parent
     * When true, any matching rows defined in $foreign_keys will be deleted
     * with the child record. When false, all DB-defined foreign keys are
     * handled per the table definition. This is only here because there's a
     * config setting to set behaviour when deleting cards
     *
     * @var bool
     */
    public bool $fk_force_delete = false;

    /** @var int number of child records found for the current record */
    public int $fk_record_count = 0;

    /**
     * If 1 or 2, show a message to the user confirming to proceed with
     * deletion of the current record when child records are found. (If
     * 2, the message includes text about permanent deletion.)
     *
     * @var self::CONFIRM_*
     */
    public int $fk_confirm_delete = self::CONFIRM_WARN;

    // Delete Message for FK
    public string $delete_message_confirm_fk;

    private bool $FG_ENABLE_LOG = ENABLE_LOG;

    /** @var string The CSRF token for the current request */
    public string $FG_CSRF_TOKEN;

    /**
     * @param string $tablename the table name of the object we're working with
     * @param string $instance_name a label for the object
     * @param string $primary_key the primary key of the table (if joining tables, make sure it's unambiguous)
     * @param Builder|null $builder a custom query builder instance (eventually will replace the other parameters)
     */
    public function __construct(string $tablename, string $instance_name, string $primary_key = "", ?Builder $builder = null)
    {
        if (class_exists(Console::class)) {
            Console::log('Construct FormHandler');
            Console::logMemory($this, 'FormHandler Class : Line ' . __LINE__);
            Console::logSpeed('FormHandler Class : Line ' . __LINE__);
        }
        self::$Instance = $this;

        $this->listView = new ListView($this);
        $this->editForm = new EditForm($this);

        $this->FG_INSTANCE_NAME = $instance_name;
        if ($primary_key !== "") {
            $this->update_query_conditions = [$primary_key => "%id"];
        } else {
            $primary_key = "$tablename.id";
        }
        $this->FG_QUERY_PRIMARY_KEY = $primary_key;

        if (is_null($builder)) {
            $this->query_builder = Connection::getConnection($tablename)
                ->orderBy($primary_key);
        } else {
            $this->query_builder = $builder;
        }
        $this->query_builder->orderBy($primary_key);

        if (strtolower($_SERVER["REQUEST_METHOD"]) === "post") {
            $posted_token = $_POST["csrf_token"] ?? "";
            $session_token = $_SESSION['CSRF_TOKEN'] ?? "";

            // Check CSRF
            if ($session_token !== $posted_token) {
                echo "CSRF Error!";
                exit();
            } else {
                //Remove key from the session
                unset($_SESSION['CSRF_TOKEN']);
            }
        }
        if (empty($_REQUEST["popup_select"])) {
            // Initializing anti csrf token (Generate a key, concat it with salt and hash it)
            $this->FG_CSRF_TOKEN = hash('SHA256', CSRF_SALT . $this->genCsrfTokenKey());
            $_SESSION['CSRF_TOKEN'] = $this->FG_CSRF_TOKEN;
        }

        $inst = strtolower($this->FG_INSTANCE_NAME);
        $this->list_message_empty = sprintf(_("No %s have been created"), $inst);
        $this->list_message_intro = sprintf(_("%s list"), ucfirst($inst));
        $this->edit_message_intro = sprintf(_("Editing %s"), $inst);
        $this->delete_message_result = sprintf(_("Your %s has been deleted."), $inst);
        $this->delete_message_intro = sprintf(_("If you really want to remove this %s, click the “Delete” button"), $inst);
        $this->add_message_intro = sprintf(_("Creating new %s"), $inst);
        $this->add_message_result = sprintf(_("Your new %s has been created."), $inst);
        $this->add_message_error = sprintf(_("There was an error creating your %s."), $inst);
        $this->edit_message_error = sprintf(_("There was an error editing your %s."), $inst);
        $this->delete_message_error = sprintf(_("There was an error deleting your %s."), $inst);

        $this->delete_message_confirm_fk = _("Are you sure you want to delete all records connected to this instance?");
    }

    public function cloneQueryBuilder(bool $conditions = true, bool $joins = false): Builder
    {
        $builder = $this->query_builder->clone();
        if ($joins === false) {
            $builder->joins = null;
            $builder->bindings["join"] = [];
        }
        if ($conditions === false) {
            $builder->wheres = [];
            $builder->bindings["where"] = [];
        }
        $builder->offset = null;
        $builder->limit = null;
        $builder->reorder();

        return $builder;
    }

    /*
    * Generate a csrf token
    */
    private function genCsrfTokenKey(): string
    {
        $token1 = microtime();
        $token2 = uniqid("", true);
        $token3 = session_id();
        $token4 = mt_rand();

        return base64_encode($token1 . $token2 . $token3 . $token4);
    }

    public static function GetInstance(): FormHandler
    {
        return self::$Instance;
    }

    public function getProcessed(): array
    {
        $processed = [];
        $vars = array_merge($_GET, $_POST);
        foreach ($vars as $key => $value) {
            // post fields can't contain . so they are replaced with ^^ at render time
            // todo: find out if there's a reason both versions are kept
            if (str_contains($key, "^^")) {
                $processed[$key] ??= $value;
                $key = str_replace("^^", ".", $key);
            }
            $processed[$key] ??= $value;
        }

        return $processed;
    }

    /**
     * Adds to the conditions a comparison between a column and a posted value
     *
     * @param string $left_column the column name
     * @param numeric-string $operator the comparison operator: 1=eq 2=lte 3=lt 4=gt 5=gte 6=ne
     * @param string $val value for the comparison
     * @return void
     */
    protected function do_field_duration(string $left_column, string $operator = "1", string $val = ""): void
    {
        if ($val === "") {
            return;
        }
        $op = (int)$operator;

        $this->query_builder->where(
            $left_column,
            match ($op) {
                default => "=",
                2 => "<=",
                3 => "<",
                4 => ">",
                5 => ">=",
                6 => "!=",
            },
            $val
        );
    }

    /**
     * Adds to the conditions a comparison between a column and a posted value
     *
     * @param string $column the column name, also used for the post field name
     * @param numeric-string $like_type the operator: 1=equal (default) 2=starts with 3=contains 4=ends with
     * @param string $val value for the comparison
     * @return void
     */
    protected function do_field(string $column, string $like_type = "1", string $val = ""): void
    {
        if ($val === "") {
            return;
        }
        $op = (int)$like_type;

        $this->query_builder->whereLike($column, match ($op) {
            default => $val,
            2 => "$val%",
            3 => "%$val%",
            4 => "%$val",
        });
    }

    /**
     * Function to execture the appropriate action:
     *  ask-add: show the form for adding a new item
     *  add: save the new item
     *  ask-edit: show the form for editing an item
     *  edit: save the changes to the item
     *  ask-delete: show the form asking to delete the item
     *  ask-delete-confirm: second confirmation for foreign key records
     *  delete: delete the item
     *  add-content: add an item to a has-many element
     *  del-content: delete and item from a has-many element
     *
     * @var string $form_action
     * @return array
     */
    public function perform_action(string &$form_action): array
    {
        if (class_exists(Console::class)) {
            Console::log('FormHandler -> perform_action');
            Console::logMemory(null, 'FormHandler -> perform_action : Line ' . __LINE__);
            Console::logSpeed('FormHandler -> perform_action : Line ' . __LINE__);
        }

        $processed = $this->getProcessed();
        //security check
        $self = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        switch ($form_action) {
            case "ask-add":
            case "add":
                if (!$this->allowAdds) {
                    header("Location: $self");
                    die();
                }
                break;
            case "ask-edit":
            case "edit":
                if (!$this->allowEdits || empty($processed["id"])) {
                    header("Location: $self");
                    die();
                }
                break;
            case "ask-del-confirm":
            case "ask-delete":
            case "delete":
                if (!$this->allowDeletes || empty($processed["id"])) {
                    header("Location: $self");
                    die();
                }
                break;
        }

        if (!empty($processed["id"])) {
            // insert the id as a condition for updates or showing an item ("ask-edit")
            $this->query_builder->where($this->FG_QUERY_PRIMARY_KEY, $processed["id"]);
            $this->update_query_conditions = array_map(
                fn ($v) => str_replace("%id", $processed["id"], $v),
                $this->update_query_conditions
            );
        }

        switch ($form_action) {
            case "add":
                $this->perform_add($form_action);
                break;
            case "edit":
                $this->perform_edit($form_action);
                break;
            case "delete":
                $this->perform_delete();
                break;
            case "export-csv":
                $this->exportData("csv");
                break;
            case "export-xml":
                $this->exportData("xml");
                break;
        }

        $processed = $this->getProcessed();

        if ($form_action === "ask-delete" && in_array($processed['id'], $this->FG_DELETION_FORBIDDEN_ID)) {
            $this->gotoLocation($this->FG_LOCATION_AFTER_DELETE ?? $self);
        }

        $list = [];
        if ($form_action === "list") {
            $dir = "asc";
            $order = $processed["order"] ?? "";
            if ($order) {
                if (in_array(strtolower($processed["sens"] ?? ""), ["asc", "desc"])) {
                    $dir = strtolower($processed["sens"]);
                }
                $this->query_builder->reorder($order, $dir);
            }

            $current_page = (int)($processed["current_page"] ?? 0);

            // default page size
            $this->query_builder->limit ??= 10;
            $current_size = 0;
            // check for existing custom size
            $session_limit = $this->query_builder->from . "-displaylimit";
            if (array_key_exists($session_limit, $_SESSION) && (int)$_SESSION[$session_limit]) {
                $current_size = (int)$_SESSION[$session_limit];
            }
            // check for newly posted custom size
            if (!empty($processed['mydisplaylimit'])) {
                if ($processed['mydisplaylimit'] === 'ALL') {
                    $current_size = 5000;
                } elseif ((int)$processed['mydisplaylimit'] > 0) {
                    $current_size = (int)$processed['mydisplaylimit'];
                }
                $_SESSION[$session_limit] = $current_size;
            }
            if ($current_size) {
                $this->query_builder->limit($current_size);
            }

            // instance_primary_key is used to fill in links for edit/delete buttons
            $this->query_builder->addSelect("$this->FG_QUERY_PRIMARY_KEY AS instance_primary_key");

            // this sets up the conditions based on searches etc
            $this->prepare_list_subselection($form_action);

            // Code here to call the Delete Selected items Fucntion
            if (isset($processed['deleteselected'])) {
                $this->Delete_Selected();
            }

            $this->FG_LIST_VIEW_ROW_COUNT = $this->query_builder->getCountForPagination();
            $list = $this->query_builder->offset($current_page * $this->query_builder->limit)->get()->toArray();

            if ($this->FG_LIST_VIEW_ROW_COUNT <= $current_size) {
                $this->FG_LIST_VIEW_PAGE_COUNT = 1;
            } else {
                $this->FG_LIST_VIEW_PAGE_COUNT = ceil($this->FG_LIST_VIEW_ROW_COUNT / $this->query_builder->limit);
            }
        } elseif (
            $form_action === "edit" || $form_action === "ask-delete" ||
            $form_action === "ask-edit" || $form_action === "add-content" || $form_action === "del-content" ||
            $form_action === "ask-del-confirm"
        ) {
            $cols = array_column(
                array_filter(
                    $this->editForm->getElements(except: [HasMany::class]),
                    // "has many" have a fake index, some fields may have a numeric one
                    fn ($v, $k) => !is_numeric($k),
                    ARRAY_FILTER_USE_BOTH
                ),
                "column"
            );

            try {
                $list = $this->query_builder->select($cols)->get()->toArray();
                $this->QUERY_RESULT = true;
            } catch (Throwable $e) {
                $this->edit_message_error .= " " . $e->getMessage();
                $this->QUERY_RESULT = false;
            }
        }

        return $list;
    }

    /**
     * Function to prepare the clause from the session filter
     *
     * @public
     */
    public function prepare_list_subselection($form_action): void
    {
        $processed = $this->getProcessed();

        $searches = $this->listView->getSearchElements();
        $filters = $this->listView->getFilters();
        if ($form_action !== "list" || (count($searches) === 0 && count($filters) === 0)) {
            return;
        }

        foreach ($filters as $i => $filter) {
            $val = $processed["filterprefix$i"] ?? "";
            if ($val) {
                $this->query_builder->whereLike($filter->column, "$val%");
            }
        }

        if ($processed['cancelsearch'] ?? false) {
            $this->listView->clearSearchSession();
            return;
        }

        // we have not posted a new search
        if (intval($processed['posted_search'] ?? 0) !== 1) {
            if ($this->listView->getSearchSession()) {
                // there was a previous search, load the inputs from the session
                $element_arr = json_decode($this->listView->getSearchSession(), true) ?: [];
                foreach ($element_arr as $entity_name => $entity_value) {
                    $processed[$entity_name] = $entity_value;
                    // for subsequent calls to $this->getProcessed()
                    $_POST[$entity_name] = $entity_value;
                }
            } else {
                // this isn't a newly posted search, so we're done
                return;
            }
        }

        $search = [];

        foreach ($searches as $el) {
            if ($el->local) {
                continue;
            }
            switch ($el::class) {
                case Search\Text::class:
                case Search\Date::class:
                case Search\Popup::class:
                case Search\Radio::class:
                case Search\Select::class:
                    $col = $el->sanitizeColumn($el->column);
                    if (empty($processed[$col])) {
                        break;
                    }
                    $opkey = "{$col}_type";
                    $val = $processed[$col];
                    $search[$opkey] = $processed[$opkey] ?? "";
                    $search[$col] = $processed[$col];
                    $this->do_field($el->column, $processed[$opkey] ?? "", $val);
                    break;
                case Search\Comparison::class:
                    $suffixes = ["0", "1"];
                    $processed["enable_{$el->column}_0"] = true;
                    $processed["enable_{$el->column}_1"] = true;
                    // fallthrough
                case Search\DateRange::class:
                    $suffixes ??= ["start", "end"];
                    foreach ($suffixes as $i) {
                        $col = $el->sanitizeColumn("{$el->column}_$i");
                        if (empty($processed["enable_$col"]) || empty($processed[$col])) {
                            continue;
                        }
                        $opkey = "{$col}_type";
                        $val = $processed[$col] . match($i) {"start" => " 00:00:00", "end" => " 23:59:59", default => ""};
                        $search[$opkey] = $processed[$opkey] ?? "";
                        $search["enable_$col"] = "true";
                        $search[$col] = $processed[$col];
                        $this->do_field_duration($el->column, $processed[$opkey], $val);
                    }
                    unset($suffixes);
                    break;
                case Search\DateRelative::class:
                    $col = $el->sanitizeColumn($el->column . ($el->start ? "_start" : "_end") . "_relative");
                    if (empty($processed["enable_$col"]) || empty($processed[$col])) {
                        break;
                    }
                    $opkey = "{$col}_type";
                    $val = $processed[$col];
                    if (!str_contains(":", $val)) {
                        $val .= ($el->start ? " 00:00:00" : " 23:59:59");
                    }
                    $search[$opkey] = $processed[$opkey];
                    $search["enable_$col"] = "true";
                    $this->do_field_duration($el->column, $processed[$opkey], $val);
                    break;
            }
        }

        $this->listView->setSearchSession(
            array_filter($search, fn($v) => is_string($v) && strlen($v))
        );
    }

    /****************************************
     * Function to delete all pre selected records,
     * This Function Gets the selected records and delete them from DB
     ******************************************/
    public function Delete_Selected(): void
    {
        try {
            $this->query_builder->delete();
        } catch (Throwable $e) {
            $this->delete_message_error .= " " . $e->getMessage();
        }
    }

    /**
     * Function to perform the add action after inserting all data in required fields
     */
    public function perform_add(string &$form_action): void
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->all_fields_valid = true;
        $values = [];
        $split_values = [];

        foreach ($this->editForm->getElements(except: [Section::class, HasMany::class]) as $row) {
            $field = $row->column;
            if ($field === "" || $row->isDisabled() || !array_key_exists($field, $processed)) {
                continue;
            }

            if ($row instanceof Select && $row->multi && is_array($processed[$field])) {
                $values[$field] = (int)array_sum($processed[$field]);
            }

            $row->validate($processed[$field]);
            if ($row->validation_err !== true) {
                $this->all_fields_valid = false;
                $form_action = "ask-add";
                continue;
            }

            if (($row instanceof Input || $row instanceof Textarea) && $row->splittable) {
                $split_values[$field] = $row->getSplitValues($processed[$field]);
            } elseif ($row instanceof Input && $row->save_callback) {
                $values[$field] = ($row->save_callback)($processed[$field]);
            } elseif ($processed[$field] === "" && ($row instanceof Input && $row->empty_is_null)) {
                $values[$field] = null;
            } elseif ($processed[$field] !== "") {
                $values[$field] ??= $processed[$field];
            }
        }

        if ($this->all_fields_valid === false) {
            $this->QUERY_RESULT = false;

            return;
        }

        if (count($split_values)) {
            foreach ($split_values as $key => $array) {
                foreach ($array as $array_value) {
                    $values[$key] = $array_value;
                    try {
                        $id = $this->query_builder->insertGetId($values);
                        // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
                        if (is_callable($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)) {
                            ($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)($id, $values);
                        }
                    } catch (Throwable $e) {
                        $this->add_message_error .= " " . $e->getMessage();
                        break;
                    }
                }
            }
        } else {
            try {
                $id = $this->query_builder->insertGetId($values);
                // CALL DEFINED FUNCTION AFTER THE ACTION ADDITION
                if (is_callable($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)) {
                    ($this->FG_ADDITIONAL_FUNCTION_AFTER_ADD)($id, $values);
                }
            } catch (Throwable $e) {
                $this->add_message_error .= " " . $e->getMessage();
            }
        }
        $this->QUERY_RESULT = $id ?: false;

        if ($this->QUERY_RESULT) {
            if ($this->FG_ENABLE_LOG) {
                Logger::insertLog(
                    Admin::id(),
                    2,
                    sprintf(_("New %s created"), $this->FG_INSTANCE_NAME),
                    _("User added a new record in database"),
                    $this->query_builder->from,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['REQUEST_URI'],
                    array_keys($values),
                    array_values($values)
                );
            }
            $this->gotoLocation($this->FG_LOCATION_AFTER_ADD ?? "?form_action=ask-edit&id=", $this->QUERY_RESULT);
        }
    }

    /**
     * Function to edit the fields
     */
    public function perform_edit(string &$form_action): void
    {
        $processed = $this->getProcessed();
        $this->all_fields_valid = true;
        $values = [];

        foreach ($this->editForm->getElements(except: [Section::class, HasMany::class]) as $row) {
            $field = $row->column;
            if ($field === "" || $row->isDisabled() || !array_key_exists($field, $processed)) {
                continue;
            }

            if ($row instanceof Select && $row->multi && is_array($processed[$field])) {
                $values[$field] = (int)array_sum($processed[$field]);
            }

            $row->validate($processed[$field]);
            if ($row->validation_err !== true) {
                $this->all_fields_valid = false;
                $form_action = "ask-edit";
                continue;
            }

            if ($processed[$field] === "" && $row instanceof Input && $row->empty_is_null) {
                $values[$field] = null;
            } elseif ($row instanceof Input && $row->save_callback) {
                $values[$field] = ($row->save_callback)($processed[$field]);
            } else {
                $values[$field] ??= $processed[$field];
            }
        }

        if ($this->all_fields_valid === false) {
            $this->QUERY_RESULT = false;
            return;
        }

        if (is_callable($this->FG_ADDITIONAL_FUNCTION_BEFORE_EDIT)) {
            ($this->FG_ADDITIONAL_FUNCTION_BEFORE_EDIT)($processed["id"], $values);
        }

        try {
            $this->QUERY_RESULT = $this->query_builder->update($values);
        } catch (Throwable) {
        }

        if ($this->QUERY_RESULT) {
            if ($this->FG_ENABLE_LOG) {
                Logger::insertLog(
                    Admin::id(),
                    3,
                    sprintf(_("Existing %s updated"), $this->FG_INSTANCE_NAME),
                    _("User edited a record in database"),
                    $this->query_builder->from,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['REQUEST_URI'],
                    array_keys($values),
                    array_values($values)
                );
            }

            $this->gotoLocation($this->FG_LOCATION_AFTER_EDIT ?? "?form_action=list");
        }
    }

    /**
     * Function to delete a record
     *
     * @public
     */
    public function perform_delete(): void
    {
        $processed = $this->getProcessed();  //$processed['firstname']
        $this->all_fields_valid = true;

        $this->QUERY_RESULT = $this->query_builder->limit(1)->delete($this->update_query_conditions);
        if ($this->QUERY_RESULT) {
            if ($this->FG_ENABLE_LOG) {
                Logger::insertLog(
                    Admin::id(),
                    3,
                    "A " . strtoupper($this->FG_INSTANCE_NAME) . " DELETED",
                    "A RECORD IS DELETED, EDITION CLAUSE USED IS " . array_kv($this->update_query_conditions),
                    $this->query_builder->from,
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['REQUEST_URI']
                );
            }
            if (is_callable($this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE)) {
                ($this->FG_ADDITIONAL_FUNCTION_AFTER_DELETE)($processed["id"]);
            }

            $this->gotoLocation($this->FG_LOCATION_AFTER_DELETE ?? "?form_action=list");
        } else {
            $this->delete_message_error .= (" " . _("There was a database error."));
        }
    }

    /**
     * Redirect to a location after add/edit/delete, appending current page state
     *
     * @param string $location
     * @param int|string|null $id
     * @return void
     */
    private function gotoLocation(string $location, int|string|null $id = null): void
    {
        if (empty($location)) {
            return;
        }
        $processed = $this->getProcessed();
        $params = [
            "current_page" => $processed["current_page"] ?? 0,
            "order" => $processed["order"] ?? "",
            "sens" => $processed["sens"] ?? "",
        ];
        $qs = (str_contains($location, "?") ? "&" : "?")
            . http_build_query(array_filter($params));
        if ($qs === "?" || $qs === "&") {
            $qs = "";
        }
        if (str_ends_with($location, "id=")) {
            $location .= urlencode($id ?? $processed["id"] ?? "");
        }
        header("Location: $location$qs");
        die();
    }

    /**
     * Checks for child records, populates FormHandler::$FG_FK_RECORDS_COUNT
     *
     * @return bool whether there are any child records
     */
    public function check_child_records(): bool
    {
        $processed = $this->getProcessed();
        if (empty($this->foreign_keys) || empty($processed["id"])) {
            return false;
        }
        $rowcount = 0;
        foreach ($this->foreign_keys as $table => $column) {
            $rowcount += Connection::getConnection($table)
                ->where($column, $processed['id'])
                ->count();
        }
        $this->fk_record_count = $rowcount;

        return ($rowcount > 0);
    }

    /**
     * Add content from HasMany and custom SQL selects (only used in FG_var_[tariffgroup|agent|service|card].inc)
     *
     * @var string $index the index within $this->FG_EDIT_FORM_ELEMENTS
     * @var int $id the id of the object to be used as foreign key
     */
    public function perform_add_content(string $index, int $id): void
    {
        $entry = $this->editForm->getElement($index);
        if (!$entry instanceof HasMany) {
            return;
        }
        $processed = $this->getProcessed();
        $table = is_null($entry->pivot_table)
            ? $entry->builder
            : Connection::getConnection($entry->pivot_table);
        $column = $entry->insert_column;
        $value = $processed["add-content-value"];
        $entry->validate($value);
        if ($entry->validation_err !== true) {
            $this->all_fields_valid = false;

            return;
        }
        $foreign_key = $entry->foreign_key;
        $table->insert([$column => $value, $foreign_key => $id]);
    }


    /**
     * Delete content from SQL selects (only used in FG_var_[tariffgroup|agent|service].inc)
     *
     * @var string $index the index within $this->FG_EDIT_FORM_ELEMENTS
     * @var int $id the id of the object to be used as foreign key
     */
    public function perform_del_content(string $index, int $id): void
    {
        $entry = $this->editForm->getElement($index);
        if (!$entry instanceof HasMany) {
            return;
        }

        $processed = $this->getProcessed();
        if (!is_null($entry->pivot_table)) {
            $table = Connection::getConnection($entry->pivot_table);
            $column = $entry->insert_column;
        } else {
            $table = $entry->builder;
            $column = $table->columns[0];
        }
        $value = $processed["del-content-value"];
        $foreign_key = $entry->foreign_key;
        $table->where([$column => $value, $foreign_key => $id])->delete();
    }


    /**
     * Function to create the top page section
     * todo: return, don't echo
     */
    public function create_toppage(string $form_action): void
    {
        $help = "";
        $msg = "";
        if ($form_action === "ask-edit" || $form_action === "edit" || $form_action === "add-content" || $form_action === "del-content") {
            if ($form_action === "ask-edit") {
                $help = $this->edit_help_text ?: $this->help_text;
            }
            $msg = $this->edit_message_intro;
        } elseif ($form_action === "ask-add") {
            $msg = $this->add_message_intro;
            $help = $this->add_help_text ?: $this->help_text;
        } elseif ($form_action === "ask-delete") {
            if (is_callable($this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE)) {
                // this function can insert a warning into the page top before delete is done
                $processed = $this->getProcessed();
                ($this->FG_ADDITIONAL_FUNCTION_BEFORE_DELETE)($processed[$this->FG_QUERY_PRIMARY_KEY]);
            }
            $msg = $this->delete_message_intro;
            $help = $this->del_help_text ?: $this->help_text;
        } elseif ($form_action === "list") {
            $help = $this->list_help_text ?: $this->help_text;
        }

        if ($help) {
            $help = "<div class='row pb-3 align-items-center' id='create_toppage_help'><div class='col'>$help</div></div>";
        }
        if ($msg) {
            $msg = "<div class='row pb-3 align-items-center' id='create_toppage'><div class='col'>$msg</div></div>";
        }

        echo $help . $msg;

        $this->create_actionfinish($form_action);
    }

    /**
     * Display a result message after add or delete is complete
     * @todo: show this on edit as well
     */
    public function create_actionfinish($form_action): void
    {
        $class = $this->QUERY_RESULT ? "success" : "danger";
        if (!empty($_POST) && str_ends_with($form_action, "delete")) {
            $msg = $this->QUERY_RESULT
                ? $this->delete_message_result
                : $this->delete_message_error;
        } elseif (!empty($_POST) && str_ends_with($form_action, "add")) {
            $msg = $this->QUERY_RESULT
                ? $this->add_message_result
                : $this->add_message_error;
        } elseif (str_ends_with($form_action, "edit") && !$this->QUERY_RESULT) {
            $msg = $this->edit_message_error;
        } else {
            return;
        }
        $html = "<div class='row pb-3' id='create_actionfinish'><div class='col'><p class='alert alert-$class'>$msg</p></div></div>";
        echo $html;
    }

    /**
     *  CREATE_CUSTOM : Function to display a custom message using form_action
     *
     * @public        TODO : maybe is better to allow use a string as parameter
     */
    public function create_custom($form_action): void
    {
        $msg = "$form_action " . _("Done");
        $html = "<div class='row pb-3 align-items-center' id='create_custom'><div class='col'><strong>$msg</strong></div></div>";
        echo $html;
    }

    /**
     * Function to create the form
     *
     * @todo make this return a string, not echo
     */
    public function create_form(string $form_action, array $list): void
    {
        if (class_exists(Console::class)) {
            Console::logSpeed('Time taken to get to line ' . __LINE__);
        }
        $processed = $this->getProcessed();

        // passed by javascript functions for add-content and del-content
        $edit_form_index = $processed["form_el_index"] ?? 0;

        if ($form_action === "ask-delete") {
            $this->check_child_records();
        }

        switch ($form_action) {
            case "add-content":
                $this->perform_add_content($edit_form_index, $processed['id']);
                echo $this->editForm->render($processed, $list, $form_action);
                break;

            case "del-content":
                $this->perform_del_content($edit_form_index, $processed['id']);
                echo $this->editForm->render($processed, $list, $form_action);
                break;

            case "ask-edit":
            case "edit":
            case "ask-add":
            case "ask-delete":
            case "ask-del-confirm":
                echo $this->editForm->render($processed, $list, $form_action);
                break;

            case "list":
                echo $this->listView->render($list);
                break;

            case "delete":
            case "add":
                $this->create_actionfinish($form_action);
                break;

            default:
                $this->create_custom($form_action);
                break;
        }
    }

    /**
     * Do multi-page navigation.  Displays the prev, next and page options.
     *
     * @param int $page the page currently viewed (one-based, unlike URL parameter which is zero-based)
     * @param int $pages the total number of pages
     * @param string $url the url to refer to with the page number inserted
     */
    public static function printPages(int $page, int $pages, string $url): string
    {
        // the number of pages on either side of the current page
        $window = 4;

        if ($pages <= 1) {
            return "";
        }

        $url = rawurldecode($url);
        $prevlabel = _("Previous");
        $prevlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", $page - 2, $url),
            $prevlabel
        );
        $prevdis = "";
        $firstlabel = _("First");
        $firstlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", 0, $url),
            $firstlabel
        );
        if ($page <= 1) {
            $prevdis = "disabled";
            $prevlink = sprintf("<span class='page-link'>%s</span>", $prevlabel);
            $firstlink = sprintf("<span class='page-link'>%s</span>", $firstlabel);
        }

        $lastlabel = _("Last");
        $lastlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", $pages - 1, $url),
            $lastlabel
        );
        $nextlabel = _("Next");
        $nextlink = sprintf(
            "<a class='page-link' href='%s'>%s</a>",
            str_replace("%s", $page, $url),
            $nextlabel
        );
        $nextdis = "";
        if ($page >= $pages) {
            $nextdis = "disabled";
            $lastlink = sprintf("<span class='page-link'>%s</span>", $lastlabel);
            $nextlink = sprintf("<span class='page-link'>%s</span>", $nextlabel);
        }

        $ret = <<< HTML
        <nav aria-label="page navigation">
            <ul class="pagination pagination-sm justify-content-center m-0">
                <li class="page-item $prevdis">
                    $firstlink
                </li>
                <li class="page-item $prevdis">
                    $prevlink
                </li>

        HTML;

        if ($page < $window * 2) {
            // |1 2 [3] 4 5...
            $min_page = 1;
            $max_page = min(2 * $window, $pages);
        } elseif ($pages >= $page + $window) {
            // ...4 5 [6] 7 8...
            $min_page = $page - $window;
            $max_page = $page + $window;
        } else {
            // ...6 7 [8] 9 10|
            $min_page = ($page - (2 * $window - ($pages - $page)));
            $max_page = $pages;
        }

        // Make sure min_page is always at least 1
        // and max_page is never greater than $pages
        $min_page = max($min_page, 1);
        $max_page = min($max_page, $pages);

        for ($i = $min_page; $i <= $max_page; $i++) {
            $link = str_replace("%s", $i - 1, $url);
            $aria = $act = "";
            if ($i === $page) {
                $act = "active";
                $aria = 'aria-current="page"';
            }
            $ret .= <<< HTML
                    <li class="page-item $act" $aria><a class="page-link" href="$link">$i</a></li>

            HTML;
        }
        $ret .= <<< HTML
                <li class="page-item $nextdis">
                    $nextlink
                </li>
                <li class="page-item $nextdis">
                    $lastlink
                </li>
            </ul>
        </nav>

        HTML;

        return $ret;
    }

    public function csrf_inputs(): string
    {
        if (!empty($this->FG_CSRF_TOKEN)) {
            return "<input type='hidden' name='csrf_token' value='$this->FG_CSRF_TOKEN'/>\n";
        }

        return "";
    }

    /**
     * @param string[] $columns
     * @return void
     */
    public function exportColumns(array $columns = ["*"]): void
    {
        if ($columns !== ["*"]) {
            $table = $this->query_builder->from;
            $table_cols = array_column(
                Connection::getConnection()->getSchemaBuilder()->getColumns($table),
                "name",
            );
            $table_cols = array_map(
                fn ($v) => "`cc_card`.`" . preg_replace("/\W/", "", $v) . "`",
                $table_cols
            );
            $columns = array_intersect($table_cols, $columns);
        }
        $this->exportColumns = $columns;
    }

    public function exportData(string $filetype): never
    {
        $this->prepare_list_subselection("list");
        $columns = implode(", ", $this->exportColumns);
        $qbc = $this->cloneQueryBuilder();
        $qbc->columns = null;
        $export_data = $qbc->selectRaw($columns)
            ->get()
            ->map(
                // todo: numeric keys in result
                fn ($row) => array_filter($row, fn($k) => !is_numeric($k), ARRAY_FILTER_USE_KEY)
            );
        if ($export_data->isEmpty()) {
            if ($err = Connection::getConnection()->getPdo()->errorCode()) {
                $export_data = [["error" => $err]];
            }
        }

        $date = (new DateTime())->format("Ymd");
        $filename = sprintf("%s dump %s.%s", $this->FG_INSTANCE_NAME, $date, $filetype);
        header("Content-Disposition: attachment;filename=$filename");
        if ($filetype === "csv") {
            header("Content-Type: text/csv");
            $h = fopen("php://output", "w");
            foreach ($export_data as $data) {
                fputcsv($h, $data, escape: "");
            }
            fclose($h);
        } else {
            // xml format:
            // <data>
            //     <header>
            //         <column name="account"/>
            //     </header>
            //     <records count="1">
            //         <row index="0">
            //             <column name="account">1234</column>
            //         </row>
            //     </records>
            // </data>
            header("Content-Type: application/xml");
            try {
                $dom = new DOMDocument();
                $data = $dom->createElement("data");
                $head = $dom->createElement("header");
                foreach (array_keys($export_data[0]) as $col) {
                    $column = $dom->createElement("column");
                    $column->setAttribute("name", $col);
                    $head->appendChild($column);
                }
                $data->appendChild($head);
                $records = $dom->createElement("records");
                $records->setAttribute("count", strval(count($export_data)));
                foreach ($export_data as $i => $line) {
                    $row = $dom->createElement("row");
                    $row->setAttribute("index", "$i");
                    foreach ($line as $col => $value) {
                        $column = $dom->createElement("column", $value);
                        $column->setAttribute("name", $col);
                        $row->appendChild($column);
                    }
                    $records->appendChild($row);
                }
                $data->appendChild($records);
                $dom->appendChild($data);
                $dom->formatOutput = true;
                echo $dom->saveXML();
            } catch (DOMException $e) {
                printf(
                    '<?xml version="1.0" encoding="utf-8"?><error><message>%s</message></error>',
                    htmlspecialchars($e->getMessage())
                );
                exit;
            }
        }
        exit;
    }
}
