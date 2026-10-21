<?php

use A2billing\A2Billing;
use A2billing\Admin;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 *
 * @copyright   Copyright © 2004-2015 - Star2billing S.L.
 * @copyright   Copyright © 2022-2025 RadiusOne Inc.
 * @author      Belaid Arezqui <areski@gmail.com>
 * @author      Michael Newton <mnewton@goradiusone.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
**/

$menu_section = 16;
require_once __DIR__ . "/../../common/lib/admin.defines.php";
/**
 * @var A2Billing $A2B
 */

Admin::checkPageAccess(Admin::ACX_MAINTENANCE);
$upload_errs = [
    UPLOAD_ERR_INI_SIZE => _("File is too large"),
    UPLOAD_ERR_FORM_SIZE => _("File is too large"),
    UPLOAD_ERR_PARTIAL => _("Upload was interrupted"),
    UPLOAD_ERR_NO_FILE => _("No file uploaded"),
    UPLOAD_ERR_NO_TMP_DIR => _("A system error occurred"),
    UPLOAD_ERR_CANT_WRITE => _("Could not write file"),
    UPLOAD_ERR_EXTENSION => _("A system error occurred"),
];

getpost_ifset(["acc", "method", "file", "to"]);
$acc ??= "";
$method ??= "list";
$file ??= "";
$to ??= "";

$file_ext_allow = $A2B->config['webui']['file_ext_allow'] ?? [".wav"];
$upload_dir = $A2B->config['webui']['dir_store_audio'] ?? "/tmp";
$file_size_ind = $A2B->config['webui']['my_max_file_size_audio'] ?? 102400;

# Handle the MusicOnHold
if (intval($acc) > 0) {
    $file_ext_allow = $A2B->config['webui']['file_ext_allow_musiconhold'] ?? [];
    $upload_dir = ($A2B->config['webui']['dir_store_mohmp3'] ?? "") . "/acc_$acc";
}

$file_ext_allow = array_map("trim", $file_ext_allow);
$message = "";
$message_bad = false;

if ($method === "upload") {
    $uploads = 0;
    foreach ($_FILES["file"]["error"] as $k => $err) {
        if ($err !== UPLOAD_ERR_OK) {
            $message = sprintf(_("Upload error %d: %s"), $err, $upload_errs[$err]);
            continue;
        }
        $filename = basename($_FILES["file"]["name"][$k]);
        $badext = 0;
        foreach ($file_ext_allow as $ext) {
            if (str_ends_with($filename, $ext)) {
                if (filesize($_FILES["file"]["tmp_name"][$k]) <= $file_size_ind) {
                    move_uploaded_file($_FILES["file"]["tmp_name"][$k], "$upload_dir/$filename");
                    $uploads++;
                } else {
                    $message = sprintf(_("File %s was too large"), $filename);
                }
                continue(2);
            } else {
                $badext++;
            }
        }
        if ($badext === count($file_ext_allow)) {
            $message = sprintf(_("File %s was the wrong type"), $filename);
        }
    }
    if ($message && $uploads) {
        $message = sprintf(_("%d successful uploads, last error message: %s"), $uploads, $message);
        $message_bad = true;
    } elseif ($message) {
        $message = sprintf(_("Last error message: %s"), $message);
        $message_bad = true;
    } elseif ($uploads) {
        $message = sprintf("%d successful uploads", $uploads);
    } else {
        $message = _("No files uploaded");
        $message_bad = true;
    }
} elseif ($method === "delete") {
    $file = str_replace([".", "/", "\\"], "", $file ?? "");
    if (!unlink($upload_dir . "/" . $file)) {
        $message = sprintf(_("File %s not found"), $file);
        $message_bad = true;
    } else {
        $message = sprintf(_("File %s deleted"), $file);
    }
} elseif ($method === "download") {
    $file = str_replace([".", "/", "\\"], "", $file ?? "");
    $filename = basename($file);
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
} elseif ($method === "rename") {
    if (rename($upload_dir . "/" . $file, $upload_dir . "/" . $to)) {
        $message = sprintf(_("File %s renamed to %s"), $file, $to);
    } else {
        $message = sprintf(_("Could not rename file %s"), $file);
        $message_bad = true;
    }
}

$dir = null;
try {
    $dir = new DirectoryIterator($upload_dir);
} catch (Throwable) {
    $message = sprintf(_("Error reading %s; check directory exists and is readable"), $upload_dir);
}
require_once __DIR__ . "/templates/main.php";
?>
<?php if($message): ?>
<div class="alert <?= $message_bad ? "alert-danger" : "alert-info" ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif ?>

<div id="uploadModal" class="modal" aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div id="modal-title" class="modal-title">
                    <h4><?= htmlspecialchars(_("Upload files")) ?></h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row pb-1">
                    <div class="col">
                        <p>
                            <?= htmlspecialchars(_("Upload directory:")) ?> <code><?= $upload_dir ?></code>.
                            <br/>
                            <?= sprintf(htmlspecialchars(_("If the path is not correct, edit the variables %s and %s in the System Settings.")), "<code>dir_store_mohmp3</code>", "<code>dir_store_audio</code>") ?>
                        </p>
                    </div>
                </div>
                <form method="post" class="container-fluid" action="?method=upload" enctype="multipart/form-data">
                    <input type="hidden" value="<?php echo $acc?>" name="acc"/>
                    <div class="row py-1">
                        <label class="col-2 col-form-label" for="file_upload" id="file_upload_label">
                            <?= htmlspecialchars(_("File")) ?>
                        </label>
                        <div class="col">
                            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $file_size_ind ?>"/>
                            <input type="file" class="form-control" name="file[]" id="file_upload" multiple="multiple"  accept=".<?= implode(",.", $file_ext_allow) ?>"/>
                            <div class="form-text muted"><?= htmlspecialchars(sprintf(_("Allowed file types: %s"), implode(", ", $file_ext_allow))) ?></div>
                            <?php if (in_array("wav", $file_ext_allow)): ?>
                            <div class="form-text muted"><?= _(".wav files must be PCM Encoded, 16 Bits, at 8000Hz.") ?></div>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="row justify-content-end border-top pt-3 mt-3 bg-transparent">
                        <div class="col text-end">
                            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(_("Upload")) ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row pb-3 align-items-end" id="list-filter-container">
    <div class="col-auto ms-auto">
        <button class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <?= htmlspecialchars(_("Upload files")) ?>&nbsp;<span class="bi bi-16 bi-cloud-arrow-up" aria-hidden="true"></span>
        </button>
    </div>
</div>

<table class="list-view-table table table-bordered table-striped table-hover caption-top">
    <caption></caption>
    <thead>
    <tr>
        <th><?= _("Name") ?></th>
        <th><?= _("Type") ?></th>
        <th><?= _("Size") ?></th>
        <th><?= _("Action") ?></th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_null($dir)): ?>
    <tr>
        <td colspan="4">
            <?= _("No files found") ?>
        </td>
    </tr>
    <?php else: ?>
    <?php foreach ($dir as $entry): ?>
    <?php if(!$entry->isFile() || !$entry->isReadable()) continue ?>
    <tr>
        <td>
            <a href="?method=download&amp;file=<?= rawurlencode($entry->getBasename()) ?>&amp;acc=<?= $acc ?>">
                <?= htmlspecialchars($entry->getBasename()) ?>
            </a>
        </td>
        <td><?= htmlspecialchars($entry->getExtension()) ?></td>
        <td><?= round($entry->getSize() / 1024) ?> KB</td>
        <td>
            <a
                class="delete-file"
                href="?method=delete&amp;file=<?= rawurlencode($entry->getBasename()) ?>&amp;acc=<?= $acc ?>"
                title="<?= htmlspecialchars(_("Delete this file")) ?>"
                data-prompt="<?= htmlspecialchars(_("Are you sure you want to delete this file?")) ?>"
            >
                <span class="bi bi-16 text-danger bi-trash3-fill" aria-hidden="true"></span>
            </a>
            <a href="?method=download&amp;file=<?= rawurlencode($entry->getBasename()) ?>&amp;acc=<?= $acc ?>" title="<?= htmlspecialchars(_("Download this file")) ?>">
                <span class="bi bi-16 text-body bi-cloud-arrow-down" aria-hidden="true"></span>
            </a>
            <a
                class="rename-file"
                href="?method=rename&amp;file=<?= rawurlencode($entry->getBasename()) ?>&amp;acc=<?= $acc ?>"
                title="<?= htmlspecialchars(_("Delete this file")) ?>"
                data-prompt="<?= htmlspecialchars(_("Please enter the new name for the file, with extension")) ?>"
            >
                <span class="bi bi-16 text-body bi-pencil-square" aria-hidden="true"></span>
            </a>
        </td>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
    </tbody>
</table>

<script>
document.querySelectorAll("a.delete-file").forEach(function (el) {
    el.addEventListener("click", function (e) {
        if (confirm(this.dataset.prompt)) {
            fetch(this.href).then(() => location.reload()).catch(e => alert(e));
        }
        e.stopPropagation();
        e.preventDefault();
        return false;
    });
});
document.querySelectorAll("a.rename-file").forEach(function (el) {
    el.addEventListener("click", function (e) {
        let response = prompt(this.dataset.prompt);
        if (response) {
            this.href += "&to=" + response;
            fetch(this.href).then(() => location.reload()).catch(e => alert(e));
        }
        e.stopPropagation();
        e.preventDefault();
        return false;
    });
});
</script>

<?php

require_once __DIR__ . "/templates/footer.php";
