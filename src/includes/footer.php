<div class="container">
    <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top fixed-bottom sticky-bottom">
        <p class="col-md-4 mb-0 text-body-secondary">&copy; 2026 <?php echo ORGDESC ?></p> <a href="/"
            class="col-md-4 d-flex align-items-center justify-content-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none" aria-label="Logo"> <img src="<?php echo ORGLOGO ?>" alt="<?php echo ORGNAME ?>" height="32"></a>
        <ul class="nav col-md-4 justify-content-end">
            <li class="nav-item<?php echo ( PAGE_NAME === 'home' ? ' active">' : '">') ?><a class="nav-link px-2 text-body-secondary" href="/"><i class="fa fa-home"></i> Home</a></li>
            <li class="nav-item"><a href="/privacy-statement" class="nav-link px-2 text-body-secondary"><i class="fas fa-shield"></i> Privacy</a></li>
            <li class="nav-item"><a href="/terms-conditions" class="nav-link px-2 text-body-secondary"><i class="fas fa-file-lines"></i> Terms</a></li>
            <li class="nav-item<?php echo ( PAGE_NAME === 'about' ? ' active">' : '">') ?><a class="nav-link px-2 text-body-secondary" href="/about"><i class="fas fa-info-circle"></i> About</a></li>
        </ul>
    </footer>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>