<?php

namespace A2billing\Forms\Elements\Edit;

use A2billing\Forms\Elements\EditElement;
use Illuminate\Database\Query\Builder;

class HasMany extends EditElement
{
    /**
     * Create a multi-part component to insert a record into a foreign table
     *
     * @param string $label The label text
     * @param Builder $builder the table to use for display of existing records
     * @param string $insert_column the text column to edit
     * @param string $foreign_key new records will be created with this column set to the object's PK
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param bool $multiline Determines whether to use <input> or <textarea>
     * @param bool $select Determines whether to use a <select> element
     * @param string|null $pivot_table If set, $table is only used for display; $pivot table is used for updates
     * @return void
     * @see perform_add_content()
     * @see perform_del_content()
     */
    public function __construct(
        string $label,
        string $name,
        protected Builder $builder,
        protected string $insert_column,
        protected string $foreign_key,
        mixed $validator = null,
        protected bool $multiline = false,
        protected bool $select = false,
        protected ?string $pivot_table = null,
    )
    {
        $this->label = $label;
        $this->name = $name;
        $this->validator = $validator;
        $this->type = "HAS_MANY";
    }

    protected function renderElement(): string
    {
        $name = htmlspecialchars($this->name);
        $label = htmlspecialchars($this->label);
        $return = sprintf("<ul class=\"list-group\" aria-labelledby=\"item%s_label\">", $this->index);
        $columns = array_map(
            fn (string $v) => substr($v, (strpos($v, ".") ?: -1) + 1),
            $this->builder->getColumns()
        );
        $entries = $this->builder
            ->clone()
            ->where($this->foreign_key, $this->value)
            ->pluck($columns[1], $columns[0]);
        $del = htmlspecialchars(sprintf(_("Delete %s"), $this->label));
        foreach ($entries as $index => $entry) {
            $return .= <<< HTML
            <li class="list-group-item d-flex justify-content-between align-items-center">
                $entry
                <button type="button" class="btn btn-sm btn-primary has-many-delete" data-index="$this->index" data-value="$index">
                    $del
                </button>
            </li>
            HTML;
        }
        $add_button = sprintf(
            "<button type=\"button\" class=\"btn btn-sm btn-primary has-many-add\" data-index=\"%s\" data-input-id=\"%s\">%s %s</button>",
            $this->index,
            $name,
            _("Add "),
            $label
        );
        $add = htmlspecialchars(_("Add a new entry"));
        if ($this->select) {
            $size = 0;
            $opts = $this->builder
                ->clone()
                ->whereNotIn($columns[0], $entries->pluck($columns[0]))
                ->pluck($columns[1], $columns[0])
                ->implode(function ($entry, $index) use (&$size) {
                    $size++;
                    return sprintf(
                        "<option value=\"%s\">%s</option>",
                        htmlspecialchars($index),
                        htmlspecialchars($entry)
                    );
                });
            if ($opts) {
                $return .= <<< HTML
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 me-3">
                        <label for="$name" class="form-label">
                            $add
                        </label>
                        <select id="$name" class="form-select form-select-sm" size="$size">
                            $opts
                        </select>
                    </div>
                    $add_button
                </li>
                HTML;
            }
        } else {
            $input = $this->multiline
                ? sprintf("<textarea id=\"%s\" class=\"form-control form-control-sm\" rows=\"5\"></textarea>", $name)
                : sprintf("<input id=\"%s\" class=\"form-control form-control-sm\"/>", $name);
            $return .= <<< HTML
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 me-3">
                    <label for="$name" class="form-label">$add</label>
                    $input
                </div>
                $add_button
            </li>
            HTML;
        }
        $return .= "</ul>";

        return $return;
    }
}
