    </main>
    
    <footer class="mt-auto py-4 bg-white border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <img src="<?= APP_URL ?>/assets/img/carlink-logo.png" alt="CARLINK" height="30" class="mb-3">
                    <p class="text-muted">
                        Peer-to-peer car rental platform connecting car owners with renters. 
                        Share your car and earn, or find the perfect car for your needs.
                    </p>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <h5 class="text-primary">Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= APP_URL ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="<?= APP_URL ?>/?route=explore" class="text-muted text-decoration-none">Explore Cars</a></li>
                        <?php if (isLoggedIn() && isOwner()): ?>
                            <li><a href="<?= APP_URL ?>/?route=add-car" class="text-muted text-decoration-none">List Your Car</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <h5 class="text-primary">For Owners</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= APP_URL ?>/?route=<?= isLoggedIn() && isOwner() ? 'add-car' : 'register' ?>" class="text-muted text-decoration-none">
                            List Your Car
                        </a></li>
                        <?php if (isLoggedIn() && isOwner()): ?>
                            <li><a href="<?= APP_URL ?>/?route=owner-dashboard" class="text-muted text-decoration-none">Owner Dashboard</a></li>
                            <li><a href="<?= APP_URL ?>/?route=manage-reservations" class="text-muted text-decoration-none">Manage Reservations</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="text-primary">For Renters</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= APP_URL ?>/?route=explore" class="text-muted text-decoration-none">Find a Car</a></li>
                        <?php if (isLoggedIn() && isRenter()): ?>
                            <li><a href="<?= APP_URL ?>/?route=user-dashboard" class="text-muted text-decoration-none">My Reservations</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0 text-muted">&copy; <?= date('Y') ?> CARLINK. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Main JavaScript -->
    <script src="<?= APP_URL ?>/assets/js/main.js"></script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
