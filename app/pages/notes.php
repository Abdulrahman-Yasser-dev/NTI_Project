<?php
/* ============================================================
   سرد — Notes CRUD Endpoint (app/pages/notes.php)
   ------------------------------------------------------------
   Loaded by the front controller through: ROOT . "notes"
   (same routing convention as every other page in app/pages/).

   This page is AJAX-only: it never renders HTML. It always
   responds with JSON and exits, so it must be requested
   directly (fetch/XHR) rather than navigated to as a normal page.

   Uses the project's existing helpers from app/core/function.php:
     - query($conn, $sql, $data)   → SELECT, returns rows
     - execute($conn, $sql, $data) → INSERT/UPDATE/DELETE

   Auth: uses the same session shape already used across the
   project — $_SESSION['user']['id'] — set at login/signup.

   Highlighting is intentionally NOT implemented here. The
   `selected_text` and `highlight_color` columns exist in the
   `notes` table for a future feature and are simply left NULL
   by every query in this file.
   ============================================================ */

require_once __DIR__ . "/../core/init.php";

header('Content-Type: application/json; charset=utf-8');

/* ============================================================
   AUTH GUARD
   ============================================================ */
if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error'   => 'يجب تسجيل الدخول لاستخدام الملاحظات.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

/* ============================================================
   HELPERS
   ============================================================ */

function notes_json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function notes_json_success($data = []) {
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Confirms a note belongs to the current user before update/delete.
 */
function notes_find_owned($conn, $noteId, $userId) {
    $rows = query($conn, "SELECT * FROM notes WHERE id = :id AND user_id = :user_id LIMIT 1", [
        ':id'      => $noteId,
        ':user_id' => $userId
    ]);
    return $rows ? $rows[0] : null;
}

/* ============================================================
   ROUTING (action-based, same request hits this one file)
   ============================================================ */
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* --------------------------------------------------------
       LIST — notes for the current user + current book
       GET notes?action=list&novel_id=123
       -------------------------------------------------------- */
    case 'list': {
        $novelId = isset($_GET['novel_id']) ? (int) $_GET['novel_id'] : 0;
        if ($novelId <= 0) {
            notes_json_error('novel_id مطلوب.');
        }

        $rows = query($conn, "
            SELECT id, novel_id, chapter_id, page_number, note_text, created_at, updated_at
            FROM notes
            WHERE user_id = :user_id AND novel_id = :novel_id
            ORDER BY created_at DESC
        ", [
            ':user_id'  => $userId,
            ':novel_id' => $novelId
        ]);

        notes_json_success(['notes' => $rows ?: []]);
        break;
    }

    /* --------------------------------------------------------
       CREATE — insert a new note
       POST notes  action=create, novel_id, chapter_id?, page_number?, note_text
       -------------------------------------------------------- */
    case 'create': {
        $novelId   = isset($_POST['novel_id']) ? (int) $_POST['novel_id'] : 0;
        $chapterId = (!empty($_POST['chapter_id'])) ? (int) $_POST['chapter_id'] : null;
        $pageNum   = (!empty($_POST['page_number'])) ? (int) $_POST['page_number'] : null;
        $text      = isset($_POST['note_text']) ? trim($_POST['note_text']) : '';

        if ($novelId <= 0) {
            notes_json_error('novel_id مطلوب.');
        }
        if ($text === '') {
            notes_json_error('نص الملاحظة مطلوب.');
        }
        if (mb_strlen($text) > 500) {
            notes_json_error('الملاحظة تتجاوز الحد الأقصى المسموح به (500 حرف).');
        }

        execute($conn, "
            INSERT INTO notes (user_id, novel_id, chapter_id, page_number, note_text, selected_text, highlight_color, created_at, updated_at)
            VALUES (:user_id, :novel_id, :chapter_id, :page_number, :note_text, NULL, NULL, NOW(), NOW())
        ", [
            ':user_id'     => $userId,
            ':novel_id'    => $novelId,
            ':chapter_id'  => $chapterId,
            ':page_number' => $pageNum,
            ':note_text'   => $text
        ]);

        $newId = $conn->lastInsertId();
        $note  = notes_find_owned($conn, $newId, $userId);

        notes_json_success(['note' => $note]);
        break;
    }

    /* --------------------------------------------------------
       UPDATE — edit an existing note's text
       POST notes  action=update, id, note_text
       -------------------------------------------------------- */
    case 'update': {
        $noteId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $text   = isset($_POST['note_text']) ? trim($_POST['note_text']) : '';

        if ($noteId <= 0) {
            notes_json_error('معرّف الملاحظة مطلوب.');
        }
        if ($text === '') {
            notes_json_error('نص الملاحظة مطلوب.');
        }
        if (mb_strlen($text) > 500) {
            notes_json_error('الملاحظة تتجاوز الحد الأقصى المسموح به (500 حرف).');
        }

        $existing = notes_find_owned($conn, $noteId, $userId);
        if (!$existing) {
            notes_json_error('الملاحظة غير موجودة.', 404);
        }

        execute($conn, "
            UPDATE notes
            SET note_text = :note_text, updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ", [
            ':note_text' => $text,
            ':id'        => $noteId,
            ':user_id'   => $userId
        ]);

        $note = notes_find_owned($conn, $noteId, $userId);
        notes_json_success(['note' => $note]);
        break;
    }

    /* --------------------------------------------------------
       DELETE — remove a note
       POST notes  action=delete, id
       -------------------------------------------------------- */
    case 'delete': {
        $noteId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($noteId <= 0) {
            notes_json_error('معرّف الملاحظة مطلوب.');
        }

        $existing = notes_find_owned($conn, $noteId, $userId);
        if (!$existing) {
            notes_json_error('الملاحظة غير موجودة.', 404);
        }

        execute($conn, "DELETE FROM notes WHERE id = :id AND user_id = :user_id", [
            ':id'      => $noteId,
            ':user_id' => $userId
        ]);

        notes_json_success(['id' => $noteId]);
        break;
    }

    default:
        notes_json_error('إجراء غير معروف.', 404);
}