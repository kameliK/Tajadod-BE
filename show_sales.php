<?php
// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recycle_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// استقبال البيانات من النموذج
$first = trim($_POST['first_name']);
$last = trim($_POST['last_name']);
$phone = trim($_POST['phone']);

// تأمين البيانات
$first = $conn->real_escape_string($first);
$last = $conn->real_escape_string($last);
$phone = $conn->real_escape_string($phone);

// جلب البيانات المطابقة
$sql = "SELECT * FROM providers 
        WHERE LOWER(first_name) = LOWER('$first') 
          AND LOWER(last_name) = LOWER('$last') 
          AND phone = '$phone'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جميع حركات البيع</title>
    <style>
        :root {
            --primary-color: #2E7D32;
            --primary-dark: #1B5E20;
            --primary-light: #81C784;
            --secondary-color: #00A5CF;
            --accent-color: #FFC107;
            --light-bg: #E8F5E9;
            --dark-text: #263238;
            --light-text: #FFFFFF;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            --gradient: linear-gradient(135deg, var(--primary-dark), var(--primary-color), var(--secondary-color));
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f3;
            direction: rtl;
            text-align: center;
            padding: 20px;
            position: relative;
        }

        .content-wrapper {
            max-width: 95%;
            margin: 0 auto;
            position: relative;
        }

        h2 {
            margin-bottom: 25px;
        }

        table {
            margin: 20px auto;
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 14px;
        }

        th {
            background-color: #2a9d8f;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f4f4f4;
        }

        p {
            margin-top: 30px;
            font-size: 18px;
            color: #444;
        }

        .back-button {
            display: inline-block;
            background: linear-gradient(135deg, #489b4c, #28a745, #00A5CF);
            gap: 8px;
            margin-bottom: 15px;
            padding: 12px 24px;
            color: var(--light-text);
            text-decoration: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: absolute;
            left: 0;
            top: 0;
            overflow: hidden;
        }

        .back-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 25%, rgba(255,255,255,0.2) 50%, transparent 75%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .back-button:hover {
            background-color: #66BB6A;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .back-button:hover::after {
            transform: translateX(100%);
        }

        @media (max-width: 768px) {
            .back-button {
                position: relative;
                left: auto;
                top: auto;
                margin-bottom: 20px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <a href="./Home.html" class="back-button">العودة إلى الصفحة الرئيسية</a>
        
        <h2>جميع حركات البيع لهذا المستخدم</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>رقم العملية</th>
                    <th>الاسم الأول</th>
                    <th>الاسم الثاني</th>
                    <th>مقدمة الرقم</th>
                    <th>رقم الهاتف</th>
                    <th>المادة</th>
                    <th>الكمية</th>
                    <th>يوم الجمع</th>
                    <th>من وقت</th>
                    <th>إلى وقت</th>
                    <th>العنوان</th>
                    <th>النقاط</th>
                    <th>نوع المزود</th>
                    <th>تاريخ الإنشاء</th>
                </tr>
                <?php 
                $counter = 1;
                while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($row['first_name']) ?></td>
                        <td><?= htmlspecialchars($row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['country_code']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['material']) ?></td>
                        <td><?= htmlspecialchars($row['amount']) ?></td>
                        <td><?= htmlspecialchars($row['collection_day']) ?></td>
                        <td><?= htmlspecialchars($row['collection_time_from']) ?></td>
                        <td><?= htmlspecialchars($row['collection_time_to']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= htmlspecialchars($row['points']) ?></td>
                        <td><?= htmlspecialchars($row['provider_type']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>لا توجد أي حركات بيع لهذا المستخدم.</p>
        <?php endif; ?>
    </div>

    <?php $conn->close(); ?>
</body>
</html>

