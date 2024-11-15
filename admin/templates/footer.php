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
</html>
