<body>
<div class="container">
    <h1>We're sorry!</h1>

    <div id="body">
        <p>We couldn't find the page to which you're referring. Try visiting our <a href="<?php echo base_url(); ?>">index page</a> and navigating to where you want to go!</p>
    </div>

    <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
</div>

</body>
</html>