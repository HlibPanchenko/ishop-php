<?php

use wfm\View;

// чтобы были подсказки, говорим php что код ниже будет использовать класс View
/** @var $this View */

?>

<?php
//require_once APP . "/views/parts/header.php";
$this->getPart('parts/header');
?>

<div class="container">
    <div class="row">
        <div class="col">
            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['errors']; unset($_SESSION['errors']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
//$this->getPart('Main/index');
echo $this->content;
?>


<?php
$this->getPart('parts/footer');
?>


