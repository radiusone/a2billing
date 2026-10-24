<?php

use A2billing\Forms\Elements\Search\Button;
use A2billing\Forms\Elements\Search\Select;
use A2billing\Forms\FormHandler;
/**
 * @var FormHandler $form
 * @var array $processed
 * @var bool $full_modal
 * @var bool $with_hide_button
 * @var string $action
 */
?>
<?php if ($full_modal && $with_hide_button): ?>
<div class="row pb-3 justify-content-center">
    <div class="col-auto">
        <?= $form->create_search_button() ?>
    </div>
</div>
<?php endif ?>
<form method="post" name="searchForm" id="searchForm" class="container-fluid form-striped" action="?<?= $action ?>">
    <input type="hidden" name="posted_search" value="1"/>
    <input type="hidden" name="current_page" value="0"/>
    <?= $form->csrf_inputs() ?>

<?php if ($full_modal): ?>
    <div class="modal" id="searchModal" aria-labelledby="modal-title-search" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title-search"><?= $form->search_form_title ?? sprintf(_("Search %s"), $form->FG_INSTANCE_NAME) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
<?php else: ?>

    <div class="row">
        <div class="col">
            <strong><?php echo $form->search_form_title?></strong>
        </div>
    </div>
<?php endif ?>

<?php foreach ($form->listView->getSearchElements(except: [Select::class, Button::class]) as $k => $item): ?>
    <?= $item->render($processed) ?>
<?php endforeach ?>

    <div class="row py-1">
    <?php foreach ($form->listView->getSearchElements(only: [Select::class]) as $item): ?>
        <?= $item->render($processed) ?>
    <?php endforeach ?>
    </div>

<?php if ($full_modal): ?>
                </div> <!-- .modal-body -->
                <div class="modal-footer">
<?php else: ?>
    <div class="row justify-content-end border-top pt-3 mt-3 bg-transparent">
        <div class="col text-end">
<?php endif ?>
            <?php if (strlen($_SESSION[$form->search_session_key] ?? "") > 10): ?>
                <?php if ($form->search_delete_enabled): ?>
                    <a class="btn btn-danger confirm-with-message" href="?deleteselected=true" data-message="<?= htmlspecialchars(sprintf(_("Are you sure you want to delete %d selected records?"), $form->FG_LIST_VIEW_ROW_COUNT)) ?>"><?= _("Delete") ?></a>
                <?php endif ?>
            <a class="btn btn-secondary" href="?cancelsearch=true"><?= _("Clear Search") ?></a>
            <?php endif ?>
            <?php foreach ($form->listView->getSearchElements(only: [Button::class]) as $button): ?>
                <?= $button->render($processed) ?>
            <?php endforeach ?>
            <button type="submit" class="btn btn-primary"><?= _("Search") ?></button>
<?php if ($full_modal): ?>
                </div><!-- .modal-footer -->
            </div><!-- .modal-content -->
        </div><!-- .modal-dialog -->
    </div><!-- .modal -->
<?php else: ?>
        </div><!-- .col -->
    </div><!-- .row -->
<?php endif ?>
</form>
