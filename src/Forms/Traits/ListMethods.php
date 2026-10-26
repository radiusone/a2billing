<?php

namespace A2billing\Forms\Traits;

use A2billing\Forms\Elements\List\Buttons;
use A2billing\Forms\Elements\List\FilterInput;
use A2billing\Forms\Elements\List\Mapping;
use A2billing\Forms\Elements\List\SqlMapping;
use A2billing\Forms\Elements\List\Value;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

trait ListMethods
{
    /**
     * Add a plain value to the list table view
     *
     * @param string $label the table column header
     * @param string|Expression $field the database column name
     * @param callable|null $callback a function that is passed the value (or the provided arguments) before display
     * @param array $arguments if provided, arguments to the function
     *                          %[0-9]+ are replaced with the given row values
     *                          %X is always replaced with the current value
     * @param bool $sortable whether or not to allow sort
     * @return static
     */
    public function AddListValue(string $label, string|Expression $field, ?callable $callback = null, array $arguments = [], bool $sortable = true): static
    {
        $col = new Value($field, $label, $sortable, $callback, $arguments);
        $this->listView->addColumn($col);
        $this->query_builder->addSelect($col->column);

        return $this;
    }

    /**
     * Adds a mapping to the list view table that translates a DB value to a pretty one
     *
     * @param string $label the table column header
     * @param string $field the database column name
     * @param array $map the mapping (key/value to raw/translated values)
     * @param bool $sortable whether or not to allow sort (note, will be done on raw value)
     * @return $this
     */
    public function AddListMapping(string $label, string $field, array $map, bool $sortable = true): static
    {
        $col = new Mapping($field, $label, $map, $sortable);
        $this->listView->addColumn($col);
        $this->query_builder->addSelect($col->column);

        return $this;
    }

    /**
     * Adds a mapping to the list view table that does a lookup to translate a DB value to a pretty one
     *
     * @param string $label the table column header
     * @param string|Expression $field the database column name
     * @param Builder $table a database object; first 2 columns will be used for raw and translated values
     * @param string $url if provided, the cell will be a link to this URL with the first column value appended
     * @param bool $sortable whether or not to allow sort (note, will be done on raw value)
     * @return $this
     */
    public function AddListSqlMapping(
        string $label,
        string|Expression $field,
        Builder $table,
        string $url = "",
        bool $sortable = true
    ): static
    {
        $col = new SqlMapping($field, $label, $table, $url, $sortable);
        $this->listView->addColumn($col);
        $this->query_builder->addSelect($col->column);

        return $this;
    }

    /**
     * Add a column to the database query for use in callback
     * arguments (%n) or action button conditions (|coln|)
     *
     * @param string|Expression $field
     * @return $this
     */
    public function AddListHiddenValue(string|Expression $field): static
    {
        $this->query_builder->addSelect($field);

        return $this;
    }

    public function AddListFilter(string|Expression $column, string $label): void
    {
        $this->listView->addFilterInput(
            new FilterInput($column, $label)
        );
    }

    public function AddListInfoButton(string $url): void
    {
        $this->listView->addInfoButton(
            new Buttons\InfoButton($url, $this->FG_INSTANCE_NAME)
        );
    }

    public function AddListEditButton(?string $url = null, string $condition = ""): void
    {
        $this->allowEdits = true;
        $url ??= "?form_action=ask-edit&id=";
        $this->listView->addEditButton(
            new Buttons\EditButton($url, $this->FG_INSTANCE_NAME, condition: $condition)
        );
    }

    public function AddListDeleteButton(?string $url = null, string $condition = ""): void
    {
        $this->allowDeletes = true;
        $url ??= "?form_action=ask-delete&id=";
        $this->listView->addDeleteButton(
            new Buttons\DeleteButton($url, $this->FG_INSTANCE_NAME, condition: $condition)
        );
    }

    /**
     * Add a button to the top of the list view table
     *
     * @param string $url
     * @param string $label
     * @param string $icon
     * @return void
     */
    public function AddListTopButton(string $url, string $label, string $icon = ""): void
    {
        $this->listView->addTopButton(
            new Buttons\TopButton($url, $label, $icon)
        );
    }

    public function AddListAddButton(?string $url = null, ?string $label = null, string $icon = ""): void
    {
        $url ??= "?form_action=ask-add";
        $label ??= sprintf(_("Add %s"), $this->FG_INSTANCE_NAME);
        $this->allowAdds = true;
        $this->listView->addFirstTopButton(new Buttons\TopButton($url, $label, $icon));
    }

    /**
     * Add an action button to the last column of the list view table
     *
     * @param string $url the destination link
     * @param string $label the text (used as alt if image supplied)
     * @param string $icon
     * @param string $class an HTML class to add to the button
     * @param string $match_index a column of the database results to compare against
     * @param string $match_value the value to match (see note above)
     * @return void
     */
    public function AddListActionButton(
        string $url,
        string $label,
        string $icon = "",
        string $class = "",
        string $match_index = "",
        string $match_value = ""
    ): void
    {
        $condition = "";
        if ($match_index !== "" && $match_value !== "") {
            $condition = sprintf(
                "'|%s|' === '%s'",
                preg_replace("/[\w.]/", "", $match_index),
                str_replace("'", "\\'", $match_value)
            );
        }

        $this->listView->addActionButton(
            new Buttons\ActionButton($url, $icon, $label, $condition, $class)
        );
    }

    /**
     * Add a "<SELECT>" button for use with popups. This REMOVES any existing button configurations
     *
     * @param string $url
     * @param string $label if not specified, defaults to "select"
     * @return void
     */
    public function AddListSelectActionButton(string $url = "#", string $label = ""): void
    {
        $this->listView->addActionButton(
            new Buttons\ActionButton($url, "", $label ?: _("select"), "", "popup-select-button")
        );
    }
}
