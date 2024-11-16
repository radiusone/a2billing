                </main>
            </div> <!-- div.row -->
<?php if (empty($popup_select)): ?>

            <footer class="py-3 my-4 border-top">
                <div class="col-12 text-center text-muted">
                    <?= COPYRIGHT ?>
                </div>
            </footer>
<?php endif ?>

        </div> <!-- div.container -->
        <?php !empty($profiler) && $profiler->display() ?>
    </body>
    <?php /* pages will all be served from /admin/Public, set path accordingly */?>
    <script src="../../common/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../common/lib/jquery/jquery.min.js"></script>
    <script src="../../common/lib/flot/js/jquery.flot.min.js"></script>
    <script src="../../common/lib/flot/js/plugins/jquery.flot.time.min.js"></script>
    <script src="../../common/lib/common.js"></script>
</html>
