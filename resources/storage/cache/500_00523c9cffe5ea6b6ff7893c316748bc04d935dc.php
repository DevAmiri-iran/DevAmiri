<?php $_shouldextend[1]=1; ?>

<?php $this->startSection('title', __('Server Error')); ?>
<?php $this->startSection('code', '500'); ?>
<?php $this->startSection('message', __('Server Error')); ?>

<?php if (isset($_shouldextend[1])) { echo $this->runChild('minimal'); } ?>