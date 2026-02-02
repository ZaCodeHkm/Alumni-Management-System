<?php
// Don't start session - it should already be started by the main page
?>
<div style="
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 20px;
    background:#f0f0f0;
">

    <a href="home.php" style="text-decoration:none; color:black; font-weight:bold;">
        AES
    </a>

    <a href="home.php" style="text-decoration:none; color:black; font-weight:bold;">
        Events
    </a>

    <a href="home.php" style="text-decoration:none; color:black; font-weight:bold;">
        Mentorship
    </a>

    <a href="home.php" style="text-decoration:none; color:black; font-weight:bold;">
        Job
    </a>

    <a href="home.php" style="text-decoration:none; color:black; font-weight:bold;">
        N
    </a>
    
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="user_check.php" style="text-decoration:none; color:black; font-weight:bold;">
            Admin
        </a>
    <?php endif; ?>
    
    <a href="profile.php" style="
        width:40px;
        height:40px;
        border-radius:50%;
        background:#555;
        color:white;
        display:flex;
        align-items:center;
        justify-content:center;
        text-decoration:none;
        font-weight:bold;
    ">
        <?php 
        $initial = 'U';
        if (isset($_SESSION['email'])) {
            $initial = strtoupper($_SESSION['email'][0]);
        }
        echo $initial;
        ?>
    </a>

</div>