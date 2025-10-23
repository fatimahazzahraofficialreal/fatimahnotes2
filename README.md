# fatimahnotes
Fatimah Notes is a modern journaling web app built with PHP, MySQL, HTML, CSS, and Vanilla JS. It allows users to write and organize journals with a Word-style rich text editor — including images, videos, YouTube embeds, autosave, and mood/tags system.

✨ Features

🗂 Journal Dashboard with card view

Create new journals

Rename or delete journals (⋮ menu)

📘 Journal Page

List of entries (with search, tags, moods)

Add new entries

Delete entries (⋮ menu)

✍️ Rich Text Editor (like Word)

Bold, Italic, Underline, Headings, Lists, Quotes, Links

Paste or upload images/videos

Embed YouTube or .mp4 links

💾 Autosave while typing

😌 Mood Selector (🙂 😐 😔 🔥 💡 🙏)

🕓 Sorting by last edited date

🧱 Tech Stack
Layer	Technology
Frontend	HTML, CSS, Vanilla JavaScript
Backend	PHP (MySQLi)
Database	MySQL / MariaDB
Deployment	Any PHP server (XAMPP, Laragon, etc.)
📁 Project Structure
<img width="618" height="402" alt="image" src="https://github.com/user-attachments/assets/f517467f-c559-47c2-94f9-0fe6942ffb35" />



🗄️ Database Schema (MySQL)

Create a new database (e.g., journaling2_db) and run:

CREATE TABLE journals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  content MEDIUMTEXT,
  video_path VARCHAR(255),
  color VARCHAR(20) DEFAULT '#E6D0B8',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL
);

CREATE TABLE entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  journal_id INT NOT NULL,
  title VARCHAR(150) DEFAULT 'Untitled',
  content MEDIUMTEXT,
  mood ENUM('🙂','😐','😔','🔥','💡','🙏') DEFAULT '🙂',
  tags VARCHAR(200),
  video_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_entries_journal FOREIGN KEY (journal_id) REFERENCES journals(id) ON DELETE CASCADE
);

⚙️ Configuration
backend/connect.php
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL); ini_set('display_errors', 0);

$host = 'localhost';
$user = 'root';
$pass = ''; // change if needed
$db   = 'journaling2_db';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');


⚠️ Save this file as UTF-8 (without BOM).
Avoid closing PHP tags (?>) to prevent JSON output errors.

🚀 How to Run (Local Web Version)
Option 1 — XAMPP / Laragon / WAMP

Clone this repository:

git clone https://github.com/fatimahazzahraofficialreal/fatimahnotes2.git


Move the folder to your web root:

XAMPP → C:\xampp\htdocs\fatimahnotes2

Laragon → C:\laragon\www\fatimahnotes2

Create a new MySQL database named journaling2_db.

Run the SQL schema above in phpMyAdmin or Adminer.

Update database credentials in backend/connect.php.

Start Apache + MySQL.

Open in your browser:

http://localhost/fatimahnotes2/

🧑‍💻 Usage
1️⃣ Journal Dashboard (index.php)

Click + New Journal → create a new journal.

Click on a card → open the journal page.

Click the ⋮ menu on a card:

✏️ Rename Journal

🗑 Delete Journal

2️⃣ Inside a Journal (journal.php?id=...)

Left panel: list of entries.

Click + Entry → add a new entry.

Click ⋮ → delete an entry.

Search by title/tags.

Right panel: rich text editor.

Use toolbar for formatting (bold, list, quote, etc.)

Add images/videos or embed YouTube/mp4 URLs.

Autosave triggers automatically after typing stops.

Select mood and add tags for better organization.

🧪 Troubleshooting
Problem	Solution
JSON parse error in browser	Make sure no extra spaces or BOM in PHP files. Disable closing ?> tag.
“+ New Journal” does nothing	Check console (F12 → Network). Ensure backend/save_journal.php exists and returns valid JSON.
Images/videos not uploading	Increase upload_max_filesize & post_max_size in php.ini (e.g., 10M/12M). Restart Apache.
404 on backend/...	Ensure folder structure matches above and PHP is running.

🪪 License

Licensed under the MIT License — free to use and modify for personal or commercial projects.

💡 Credits

Developed by Fatimah Az-Zahra Al-Rohimi
Information Systems — University of Pamulang (UNPAM) Serang
