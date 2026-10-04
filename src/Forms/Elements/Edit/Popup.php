<?php

namespace A2billing\Forms\Elements\Edit;

class Popup extends Input
{
    /**
     * @param string $label The label text
     * @param string $name The form input name
     * @param string $url The address of the popup
     * @param string $help_text Text to display below the form input
     * @param array $attributes HTML attributes for the input
     * @param (callable(string):(string|true))|null $validator A validation method that returns true or an error message
     * @param string $error A message to show if validation fails
     * @return void
     */
    public function __construct(
        string $label,
        string $name,
        protected string $url,
        string $help_text = "",
        array $attributes = [],
        mixed $validator = null,
        string $error = "",
    )
    {
        parent::__construct($label, $name, $help_text, $attributes, $validator, $error);
        $this->type = "POPUPVALUE";
    }

    protected function renderElement(): string
    {
        $input = parent::renderElement();
        $url = htmlspecialchars($this->url);
        $name = htmlspecialchars($this->name);
        $label = htmlspecialchars(_("open a popup to select an item"));

        return <<< HTML
        <div class="input-group">
            $input
            <a
                href="$url"
                data-window-name="{$name}Popup" 
                data-popup-options="width=750,height=450,top=50,left=100,scrollbars=1" 
                class="btn btn-primary popup_trigger" 
                aria-label="$label"
            ><span class="bi bi-box-arrow-up-right fw-bolder fs-6"></span></a>
        </div>
        HTML;
    }
}
