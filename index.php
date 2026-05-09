<?php
session_start();

/*
|--------------------------------------------------------------------------
| بيانات الكتب الأساسية
|--------------------------------------------------------------------------
*/

$libraryBooks = [

    [
        "id" => 1,
        "title" => "Clean Code",
        "author" => "Robert Martin",
        "genre" => "Technology",
        "year" => 2008,
        "pages" => 464,
        "image_url" => "https://covers.openlibrary.org/b/id/9641656-L.jpg"
    ],

    [
        "id" => 2,
        "title" => "Sapiens",
        "author" => "Yuval Noah",
        "genre" => "History",
        "year" => 2011,
        "pages" => 498,
        "image_url" => "https://covers.openlibrary.org/b/id/8167896-L.jpg"
    ],

    [
        "id" => 3,
        "title" => "Atomic Habits",
        "author" => "James Clear",
        "genre" => "Non-Fiction",
        "year" => 2018,
        "pages" => 320,
        "image_url" => "https://covers.openlibrary.org/b/id/10521270-L.jpg"
    ]
];

/*
|--------------------------------------------------------------------------
| أنواع الكتب
|--------------------------------------------------------------------------
*/

$genres = [
    "Fiction",
    "Non-Fiction",
    "Science",
    "History",
    "Biography",
    "Technology"
];

/*
|--------------------------------------------------------------------------
| متغيرات مهمة
|--------------------------------------------------------------------------
*/

$errors = [];
$formData = [];

$isEdit = false;
$currentEditId = null;

/*
|--------------------------------------------------------------------------
| البحث
|--------------------------------------------------------------------------
*/

$search = $_GET['search'] ?? '';

if ($search != "") {

    $libraryBooks = array_filter($libraryBooks, function ($book) use ($search) {

        return stripos($book['title'], $search) !== false ||
               stripos($book['author'], $search) !== false;
    });
}

/*
|--------------------------------------------------------------------------
| الفرز
|--------------------------------------------------------------------------
*/

$sort = $_GET['sort'] ?? '';

if ($sort != "") {

    usort($libraryBooks, function ($a, $b) use ($sort) {

        return $a[$sort] <=> $b[$sort];
    });
}

/*
|--------------------------------------------------------------------------
| التعديل
|--------------------------------------------------------------------------
*/

if (isset($_GET['edit_id'])) {

    $currentEditId = (int) $_GET['edit_id'];

    foreach ($libraryBooks as $book) {

        if ($book['id'] == $currentEditId) {

            $formData = $book;
            $isEdit = true;
        }
    }
}

/*
|--------------------------------------------------------------------------
| حذف كتاب
|--------------------------------------------------------------------------
*/

if (isset($_POST['delete_id'])) {

    $deleteId = (int) $_POST['delete_id'];

    $libraryBooks = array_filter($libraryBooks, function ($book) use ($deleteId) {

        return $book['id'] != $deleteId;
    });

    $_SESSION['success'] = "Book deleted successfully.";

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| معالجة الفورم
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['delete_id'])) {

    // تنظيف البيانات

    $title = htmlspecialchars(trim($_POST['title']));
    $author = htmlspecialchars(trim($_POST['author']));
    $genre = $_POST['genre'] ?? '';
    $year = (int) $_POST['year'];
    $pages = (int) $_POST['pages'];
    $imageUrl = htmlspecialchars(trim($_POST['image_url']));

    // حفظ البيانات عند الخطأ

    $formData = $_POST;

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    // Title

    if ($title == "") {

        $errors['title'] = "Title is required.";

    } elseif (strlen($title) < 3 || strlen($title) > 120) {

        $errors['title'] = "Title must be between 3 and 120 characters.";
    }

    // Author

    if ($author == "") {

        $errors['author'] = "Author name is required.";

    } elseif (str_word_count($author) < 2) {

        $errors['author'] = "Author name must contain at least 2 words.";
    }

    // Genre

    if (!in_array($genre, $genres)) {

        $errors['genre'] = "Please choose a valid genre.";
    }

    // Year

    if ($year < 1000 || $year > date("Y")) {

        $errors['year'] = "Please enter a valid year.";
    }

    // Pages

    if ($pages <= 0) {

        $errors['pages'] = "Pages must be greater than 0.";
    }

    /*
    |--------------------------------------------------------------------------
    | إذا لا يوجد أخطاء
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        // إيجاد أكبر ID

        $maxId = 0;

        foreach ($libraryBooks as $book) {

            if ($book['id'] > $maxId) {

                $maxId = $book['id'];
            }
        }

        // ID جديد

        if ($isEdit) {

            $newId = $currentEditId;

        } else {

            $newId = $maxId + 1;
        }

        // بيانات الكتاب الجديد

        $newBook = [

            "id" => $newId,
            "title" => $title,
            "author" => $author,
            "genre" => $genre,
            "year" => $year,
            "pages" => $pages,
            "image_url" => $imageUrl
        ];

        // تعديل

        if ($isEdit) {

            foreach ($libraryBooks as &$book) {

                if ($book['id'] == $currentEditId) {

                    $book = $newBook;
                }
            }

            $_SESSION['success'] = "Book updated successfully.";

        } else {

            // إضافة كتاب جديد

            $libraryBooks[] = $newBook;

            $_SESSION['success'] = "Book added successfully.";
        }

        // Redirect

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>My Library</title>

    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body{
            background:#f5f6fa;
        }

        .book-image{
            width:65px;
            height:85px;
            object-fit:cover;
            border-radius:8px;
        }

        .card{
            border:none;
            border-radius:14px;
        }

        .table{
            border-radius:10px;
            overflow:hidden;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <!-- عنوان الصفحة -->

    <div class="mb-4">

        <h2 class="fw-bold">
            📚 Personal Library Manager
        </h2>

        <p class="text-muted">
            Manage your books in a simple way.
        </p>

    </div>

    <!-- رسالة النجاح -->

    <?php if(isset($_SESSION['success'])): ?>

        <div class="alert alert-success">

            <?= $_SESSION['success']; ?>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>

    <!-- رسالة الأخطاء -->

    <?php if(!empty($errors)): ?>

        <div class="alert alert-danger">

            Please fix the errors below.

        </div>

    <?php endif; ?>

    <div class="row g-4">

        <!-- الفورم -->

        <div class="col-lg-4">

            <div class="card shadow-sm p-4">

                <h4 class="mb-4">

                    <?= $isEdit ? "Edit Book" : "Add New Book"; ?>

                </h4>

                <form method="POST">

                    <!-- Title -->

                    <div class="mb-3">

                        <label class="form-label">
                            Book Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control <?= isset($errors['title']) ? 'is-invalid' : ''; ?>"
                            value="<?= htmlspecialchars($formData['title'] ?? '') ?>"
                        >

                        <div class="invalid-feedback">

                            <?= $errors['title'] ?? ''; ?>

                        </div>

                    </div>

                    <!-- Author -->

                    <div class="mb-3">

                        <label class="form-label">
                            Author
                        </label>

                        <input
                            type="text"
                            name="author"
                            class="form-control <?= isset($errors['author']) ? 'is-invalid' : ''; ?>"
                            value="<?= htmlspecialchars($formData['author'] ?? '') ?>"
                        >

                        <div class="invalid-feedback">

                            <?= $errors['author'] ?? ''; ?>

                        </div>

                    </div>

                    <!-- Genre -->

                    <div class="mb-3">

                        <label class="form-label">
                            Genre
                        </label>

                        <select
                            name="genre"
                            class="form-select <?= isset($errors['genre']) ? 'is-invalid' : ''; ?>"
                        >

                            <option value="">
                                Select Genre
                            </option>

                            <?php foreach($genres as $genreItem): ?>

                                <option
                                    value="<?= $genreItem; ?>"
                                    <?= ($formData['genre'] ?? '') == $genreItem ? 'selected' : ''; ?>
                                >

                                    <?= $genreItem; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <div class="invalid-feedback">

                            <?= $errors['genre'] ?? ''; ?>

                        </div>

                    </div>

                    <!-- Year -->

                    <div class="mb-3">

                        <label class="form-label">
                            Year
                        </label>

                        <input
                            type="number"
                            name="year"
                            class="form-control <?= isset($errors['year']) ? 'is-invalid' : ''; ?>"
                            value="<?= htmlspecialchars($formData['year'] ?? '') ?>"
                        >

                        <div class="invalid-feedback">

                            <?= $errors['year'] ?? ''; ?>

                        </div>

                    </div>

                    <!-- Pages -->

                    <div class="mb-3">

                        <label class="form-label">
                            Pages
                        </label>

                        <input
                            type="number"
                            name="pages"
                            class="form-control <?= isset($errors['pages']) ? 'is-invalid' : ''; ?>"
                            value="<?= htmlspecialchars($formData['pages'] ?? '') ?>"
                        >

                        <div class="invalid-feedback">

                            <?= $errors['pages'] ?? ''; ?>

                        </div>

                    </div>

                    <!-- Image URL -->

                    <div class="mb-4">

                        <label class="form-label">
                            Book Cover URL
                        </label>

                        <input
                            type="text"
                            name="image_url"
                            class="form-control"
                            value="<?= htmlspecialchars($formData['image_url'] ?? '') ?>"
                        >

                    </div>

                    <button class="btn btn-primary w-100">

                        <?= $isEdit ? "Update Book" : "Add Book"; ?>

                    </button>

                </form>

            </div>

        </div>

        <!-- الجدول -->

        <div class="col-lg-8">

            <div class="card shadow-sm p-4">

                <!-- البحث والفرز -->

                <form class="row g-2 mb-4">

                    <div class="col-md-6">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search by title or author..."
                            value="<?= htmlspecialchars($search); ?>"
                        >

                    </div>

                    <div class="col-md-4">

                        <select name="sort" class="form-select">

                            <option value="">
                                Sort By
                            </option>

                            <option value="title">
                                Title
                            </option>

                            <option value="year">
                                Year
                            </option>

                            <option value="pages">
                                Pages
                            </option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <button class="btn btn-dark w-100">

                            Search

                        </button>

                    </div>

                </form>

                <!-- عدد الكتب -->

                <p class="text-muted">

                    Total Books:
                    <strong><?= count($libraryBooks); ?></strong>

                </p>

                <!-- جدول الكتب -->

                <div class="table-responsive">

                    <table class="table table-striped table-hover table-bordered align-middle">

                        <thead class="table-dark">

                        <tr>

                            <th>#</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Genre</th>
                            <th>Year</th>
                            <th>Pages</th>
                            <th width="170">Actions</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php if(count($libraryBooks) > 0): ?>

                            <?php foreach($libraryBooks as $book): ?>

                                <tr>

                                    <td>

                                        <?= $book['id']; ?>

                                    </td>

                                    <td>

                                        <img
                                            src="<?= htmlspecialchars($book['image_url']); ?>"
                                            class="book-image"
                                        >

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($book['title']); ?>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($book['author']); ?>

                                    </td>

                                    <td>

                                        <?= htmlspecialchars($book['genre']); ?>

                                    </td>

                                    <td>

                                        <?= $book['year']; ?>

                                    </td>

                                    <td>

                                        <?= $book['pages']; ?>

                                    </td>

                                    <td>

                                        <!-- Edit -->

                                        <a
                                            href="?edit_id=<?= $book['id']; ?>"
                                            class="btn btn-sm btn-warning"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                        </a>

                                        <!-- Delete -->

                                        <button
                                            class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal<?= $book['id']; ?>"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </button>

                                        <!-- Modal -->

                                        <div
                                            class="modal fade"
                                            id="deleteModal<?= $book['id']; ?>"
                                            tabindex="-1"
                                        >

                                            <div class="modal-dialog">

                                                <div class="modal-content">

                                                    <div class="modal-header">

                                                        <h5 class="modal-title">
                                                            Confirm Delete
                                                        </h5>

                                                        <button
                                                            type="button"
                                                            class="btn-close"
                                                            data-bs-dismiss="modal"
                                                        ></button>

                                                    </div>

                                                    <div class="modal-body">

                                                        Are you sure you want to delete
                                                        <strong>
                                                            <?= htmlspecialchars($book['title']); ?>
                                                        </strong>
                                                        ?

                                                    </div>

                                                    <div class="modal-footer">

                                                        <button
                                                            type="button"
                                                            class="btn btn-secondary"
                                                            data-bs-dismiss="modal"
                                                        >

                                                            Cancel

                                                        </button>

                                                        <form method="POST">

                                                            <input
                                                                type="hidden"
                                                                name="delete_id"
                                                                value="<?= $book['id']; ?>"
                                                            >

                                                            <button class="btn btn-danger">

                                                                Delete

                                                            </button>

                                                        </form>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8" class="text-center text-muted">

                                    No books found.

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>