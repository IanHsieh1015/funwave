<?php
require_once("method/pdo-connect.php");
require_once("./public/admin-if-login.php");

////總筆數 mysqli寫法
//$sqlTotal="SELECT * FROM course_schedule";
//$resultTotal=$conn->query($sqlTotal);
//$totalCourse=$resultTotal->num_rows;


$sql_query="SELECT * FROM course_schedule  ";
//準備好的語句for所有資料
$stmt=$db_host->prepare($sql_query);

//執行全部 一定要fetch出來 算出

try{
    $stmt->execute();
    $resultTotal=$stmt->fetchAll(PDO::FETCH_ASSOC);
    //取得影響的資料筆數
    $totalCourse=$stmt->rowCount();

}catch(PDOException $e){
    echo $e->getMessage();
}

if (isset($_GET["s"])&&($_GET["s"]!="")){
    $search = $_GET["s"];
    $sql="SELECT * FROM course_schedule WHERE schedule_id ='$search'";
    //準備好語句for搜尋框
    $result_query =$db_host->prepare($sql);


}
else{
    //顯示分頁
    if(isset($_GET["p"])){
        $p=$_GET["p"];
    }else{
        $p=1;

    }
    $pageItems=6;
    $startItem=($p-1)*$pageItems;

//計算總頁數
    $pageCount=$totalCourse/$pageItems;

//取餘數
    $pageR=$totalCourse%$pageItems;


    $startNo=($p-1)*$pageItems+1;
    $endNo=$p*$pageItems;

    if($pageR!==0){
        $pageCount=ceil($pageCount);//如果不=0無條件進位
        if($pageCount==$p){
            $endNo=$endNo-($pageItems-$pageR);
        }
    }

    $sql="SELECT * FROM course_schedule LIMIT $startItem, $pageItems;";
    $result_query =$db_host->prepare($sql);


}

//最後執行
try{
    $result_query ->execute();
    $resultTotal=$result_query->fetchAll(PDO::FETCH_ASSOC);
    $course_rows=$result_query->rowCount();

    //    echo $course_rows;
}catch(PDOException $e){
    echo $e->getMessage();
}





?>
<!doctype html>
<html lang="en">

<head>
    <title>Course Schedule List</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php require_once("./public/css.php") ?>
    <style>
        .nav-tabs li {
            padding: 0px 3px;
        }
    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row wrap d-flex">
        <?php require_once("./public/admin-header-logined.php"); ?> 
        <!--menu-->
        <aside class="col-lg-2 navbar-side shadow-sm">
            <?php require_once("./public/nav.php") ?>
        </aside>
        <!--/menu-->
        <div class="col-lg-9 button-group shadow-sm">
            <form class="d-flex justify-content-between align-items-center" action="" method="get">
                <div class="my-3">
                    <!-- <a role="button" href="service.php" class="btn btn-primary">返回</a> -->
                    <a role="button" href="addCourseSchedule.php" class="btn btn-primary">新增開課表</a>
                </div>
                <div class="d-flex">
                    <input class="form-control me-2" type="search" name="s" placeholder="請輸入開課代碼" value="<?php if (isset($search)) echo $search; ?>">
                    <button class="btn btn-primary text-nowrap">搜尋</button>
                </div>
            </form>
        </div>


        <article class="article col-lg-9 shadow-sm table-responsive">
            <div class="tabs-group">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="service.php">課程訂單管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="course-list.php">課程清單</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="course-schedule-list.php">開課清單</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="spot-list.php">浪點清單</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student-list.php">學生清單</a>
                    </li>
                </ul>
            </div>
            <!--content-->
            <div class="table-wrap">
                <?php if ($course_rows > 0) : ?>
                    <table class="table table-control  table-sm table-striped align-middle my-3">
                        <thead>
                        <tr>
                            <th>查看</th>
                            <th>開課代碼</th>
                            <th>教練代號</th>
                            <th>課程代碼</th>
                            <th>上課時段</th>
                            <th>編輯</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($resultTotal as $value) : ?>
                            <tr>
                                <td><a role="button" href="examineCourseSchedule.php?schedule_id=<?= $value["schedule_id"] ?>" class=""><i class="fas fa-search"></i></a></td>
                                <td><?= $value["schedule_id"] ?></td>
                                <td><?= $value["coach_id"] ?></td>
                                <td><?= $value["course_code"] ?></td>
                                <td><?= $value["course_time"] ?></td>
                                <td>
                                    <a role="button" href="updateCourseSchedule.php?schedule_id=<?= $value["schedule_id"] ?>" class=""><i class="fas fa-edit"></i></a> / 
                                    <a role="button" href="./method/deleteCourseSchedule.php?schedule_id=<?= $value["schedule_id"] ?>" class="" onclick="javascript:return del()" value="delete"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div>
                        <!--如果有分頁要顯示目前筆數-->
                        <?php if(isset($p)): ?>
                            <div class="py-2">共 <?=$totalCourse?> 筆</div>
                        <?php else: ?>
                            <div class="py-2">共 <?=$course_rows?> 筆</div>
                        <?php endif; ?>
                    </div>

                    <!--        如果使用搜尋功能因為沒有p pagaCount會跑出來有問題 所以加上判斷 有p才出現這個UI-->
                    <?php if(isset($p)): ?>
                        <nav aria-label="Page navigation example ">
                            <ul class="pagination justify-content-center">
                                <li class="page-item"><a class="page-link" href="course-list.php?p=1">第一頁</a></li>
                                <?php for($i=1;$i<=$pageCount; $i++) :?>
                                    <!--當下頁數跟頁碼相同時echo active 寫在li class裡面-->
                                    <li class="page-item <?php if($p==$i) echo "active" ?>">
                                        <a class="page-link" href="course-schedule-list.php?p=<?=$i?>"><?=$i?></a></li>
                                <?php endfor; ?>
                                <li class="page-item"><a class="page-link" href="course-list.php?p=<?=$pageCount?>"> 最末頁</a></li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else : ?>
                    沒有資料
                <?php endif; ?>
            </div>

        </article>
        <!--/content-->
    </div>
</div>

<script>

    function del() {
        var msg = "確定確定要刪除這個開課表嗎？";
        if (confirm(msg)==true){
            return true;
        }else{
            return false;
        }
    }
</script>
</body>

</html>