<?php
    session_start();

    //Check if user is logged and is an admin in otherwise redirect to home.php
    function adminCheck()
    {
        if (!isset($_SESSION['user']))
        {
            error_log("missing user, redirecting to login page!", 0);
            header("Location: home.php");
            die();
        }
        else if ($_SESSION['user']['isAdmin'])
        {
            error_log("admin logged in!"); 
        }
        else 
        {
            error_log("user is not an admin, redirecting to home page!", 0);
            header("Location: home.php");
            die();
        }
    }
    
    //allow access to every user
    function userCheck()
    {
        //Check if user is logged in otherwise redirect to index.php
        if (!isset($_SESSION['user']))
        {
            error_log("missing user, redirecting to login page!", 0);
            header("Location: index.php");
            die();
        }
        else 
        {
            error_log("user logged in!");
        }
    }

    //perform userCheck
    userCheck();

    //prints the menu for admin or user
    function printMenu()
    {
        echo '<div class="l-navbar show" id="nav-bar"><nav class="nav"><div> <a href="#" class="nav_logo"> <i class="bx bx-layer nav_logo-icon"></i> <span class="nav_logo-name">AlgoWiFi</span> </a><div class="nav_list">';
        $pageName = basename($_SERVER['PHP_SELF']);
        if ($_SESSION['user']['isAdmin'])
        {            
            echo '<a href="home.php" class="nav_link '.($pageName == "home.php" ? "active" : "").'"> <i class="bx bx-grid-alt nav_icon"></i> <span class="nav_name">Dashboard</span> </a> 
            <a href="profile.php" class="nav_link '.($pageName == "profile.php" ? "active" : "").'"> <i class="bx bx-user nav_icon"></i> <span class="nav_name">Profile</span> </a> 
            <a href="users.php" class="nav_link '.(($pageName == "users.php" || $pageName == "user.php") ? "active" : "").'"> <i class="bx bxs-user-detail nav_icon"></i> <span class="nav_name">Users</span> </a> 
            <a href="hotspots.php" class="nav_link '.(($pageName == "hotspots.php" || $pageName == "hotspot.php") ? "active" : "").'"> <i class="bx bx-network-chart nav_icon"></i> <span class="nav_name">Hotspots</span> </a> 
            <a href="campaigns.php" class="nav_link '.(($pageName == "campaigns.php" || $pageName == "campaign.php") ? "active" : "").'"> <i class="bx bx-purchase-tag-alt nav_icon"></i> <span class="nav_name">Campaigns</span> </a> 
            <a href="kmd.php" class="nav_link '.($pageName == "kmd.php" ? "active" : "").'"> <i class="bx bx-wallet nav_icon"></i> <span class="nav_name">Kmd</span> </a> ';
        }
        else 
        {
            echo '<a href="home.php" class="nav_link '.($pageName == "home.php" ? "active" : "").'"> <i class="bx bx-grid-alt nav_icon"></i> <span class="nav_name">Dashboard</span> </a> 
            <a href="profile.php" class="nav_link '.($pageName == "profile.php" ? "active" : "").'"> <i class="bx bx-user nav_icon"></i> <span class="nav_name">Profile</span> </a> ';
            if ($_SESSION['user']['isHotspotter'])
                echo '<a href="hotspots.php" class="nav_link '.(($pageName == "hotspots.php" || $pageName == "hotspot.php") ? "active" : "").'"> <i class="bx bx-network-chart nav_icon"></i> <span class="nav_name">Hotspots</span> </a>';
            if ($_SESSION['user']['isPublisher'])
                echo '<a href="campaigns.php" class="nav_link '.(($pageName == "campaigns.php" || $pageName == "campaign.php") ? "active" : "").'"> <i class="bx bx-purchase-tag-alt nav_icon"></i> <span class="nav_name">Campaigns</span> </a> ';
        }
        echo '</div></div><a href="scriptsPHP/logout.php" class="nav_link"> <i class="bx bx-log-out nav_icon"></i> <span class="nav_name">Log-Out</span> </a></nav></div>';
    }
?>