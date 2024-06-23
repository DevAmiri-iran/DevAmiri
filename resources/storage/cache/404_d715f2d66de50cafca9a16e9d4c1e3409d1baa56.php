<?php $_shouldextend[1]=1; ?>

<?php $this->startSection('title', __('Not Found')); ?>
<?php $this->startSection('code', '404'); ?>
<?php $this->startSection('message', __('Not Found')); ?>

<?php if (isset($_shouldextend[1])) { echo $this->runChild('minimal'); } ?>