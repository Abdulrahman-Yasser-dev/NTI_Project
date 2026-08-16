# 1. Final Project — Sard (سرد)

## 1.1 Project Overview

The final project of the internship was **Sard (سرد)**, a full-stack Arabic digital reading platform.

The main idea behind Sard was to create a modern web platform that connects readers and writers in one place. Instead of being only a static book catalog, the platform provides an interactive reading journey that includes book discovery, detailed book pages, chapter-based reading, personal notes, user libraries, writer publishing tools, and admin moderation.

The platform supports different user roles:
- Reader: browse books, read chapters, rate books, write comments, and manage personal reading lists.
- Writer: submit novels, manage chapters, and publish content after admin approval.
- Admin: manage users, writer requests, and novel publishing workflow.

The main sections of Sard are Home, Browse Books, Book Details, Reading Experience, Notes, Profile, Writer Dashboard, and Admin Dashboard.

## 1.2 Home Page

The Home page introduces Sard with a premium Arabic-first interface and a bookshelf-based layout.

Books displayed on the homepage are fetched dynamically from the database through the `homepage_books` table and grouped into visual shelf rows. The page also includes role-aware navigation, where actions and links change based on whether the visitor is a guest, reader, writer, or admin.

This section helps users quickly discover featured books and navigate to reading or writing flows.

## 1.3 Browse Books

The Browse Books section is the main discovery area for readers.

It includes:
- Dynamic search by title, author, description, and keywords.
- Category filtering with database-driven category chips.
- Grouped shelves by category.
- Empty-state handling when no matching books are found.

This section was designed to make large book collections easier to explore while keeping the experience visually engaging.

## 1.4 Book Details

The Book Details page provides complete information about each novel and connects the reader to engagement features.

Main features include:
- Book metadata (author, category, year, pages, language, and description).
- User library actions (add to favorites, reading now, my list, completed).
- Rating system (1 to 5 stars) with update support for existing ratings.
- Automatic recalculation of average rating and synchronization to the novels table.
- Comment system with latest user comments.
- Chapter list and related books.

This page acts as the bridge between discovery and actual reading.

## 1.5 Reading Experience

The Reading section is where users read chapters in an immersive interface.

It includes:
- Dynamic chapter loading based on selected book and chapter number.
- Reading progress calculation according to chapter position.
- Split chapter content into readable pages.
- Chapter search panel for faster navigation.
- Realistic page-turn reading UI with RTL support for Arabic text.

This section was built to improve readability and make long-form Arabic reading more comfortable and interactive.

## 1.6 Notes System

The Notes feature allows readers to save personal notes while reading.

The notes API supports full CRUD operations:
- List notes by current user and novel.
- Create a note with optional chapter/page context.
- Update existing notes.
- Delete notes.

The API validates authentication and ownership checks to ensure each user can only manage their own notes.

This feature encourages active reading and helps users retain ideas, quotes, and reflections.

## 1.7 Writer Workflow

Sard includes a complete workflow for writers to publish content.

Main writer features:
- Writer dashboard to view all novels and statuses.
- Add new novel page with cover upload, description, and categories.
- Novel submission starts as archived (pending review).
- Chapter management page with sorting and edit tools.
- Rich-text chapter editor with draft/publish states and word count.

Writers can continue improving their work while waiting for moderation and publication approval.

## 1.8 Admin Dashboard

The Admin Dashboard manages operational control of the platform.

It includes:
- Role-based user management (users, writers, admins).
- Writer request approval/rejection workflow.
- Novel request moderation (approve archived novels or delete).
- Dashboard metrics and latest user activity.
- Admin profile editing and credential updates.

This section ensures content quality and protects the publishing process.

## 1.9 Authentication and Profile

The platform includes a combined signup/login page and a profile management area.

Profile features include:
- Update username, email, profile image, and password.
- Personal reading tabs (reading now, favorites, my list).
- Writer-specific tabs for published works and author bio.
- Writer request submission for users who want to become writers.

This section personalizes the platform for each role and keeps account management simple.

# 2. My Contribution to the Final Project

I worked on Sard as a Full Stack Developer and contributed to both frontend and backend implementation.

My contribution included:
- Building and integrating role-based user flows across reader, writer, and admin experiences.
- Developing and improving core pages such as book details, reading, profile, and writer tools.
- Implementing backend database operations in PHP/PDO with MySQL for books, chapters, comments, ratings, library records, and notes.
- Connecting frontend interactions with backend endpoints and validating request data.

One of the most important backend contributions was the **book interaction system**:
- Users can rate books.
- If a rating already exists, it is updated instead of duplicated.
- The average book rating is recalculated and stored consistently.
- Readers can submit comments linked to books and user accounts.

I also contributed to the **personal library logic**:
- Readers can mark books as favorites.
- Readers can organize books into reading now, my list, and completed states.
- State changes are persisted and reflected in the profile dashboard.

Another major contribution was the **notes module**:
- I worked on the notes API structure and request flow.
- The module supports create, read, update, and delete operations.
- Access control is enforced so users can only edit/delete their own notes.

In writer and admin workflows, I contributed to:
- Novel submission and moderation flow.
- Chapter creation/editing lifecycle (draft vs published).
- Role and request handling for writer approvals.

During development, I tested full integration between frontend pages, PHP backend, and MySQL database, and resolved issues related to validation, session handling, and state synchronization.

For collaboration and version control, we used Git and GitHub, and I also contributed to technical documentation and project presentation materials.

# 3. Technologies Used

- Frontend: HTML5, CSS3, JavaScript (Vanilla JS)
- Backend: PHP
- Database: MySQL
- Local Environment: XAMPP, phpMyAdmin
- Version Control: Git, GitHub

# 4. Final Result

Sard was delivered as a functional full-stack Arabic reading platform that supports discovery, reading, note-taking, writer publishing, and admin moderation in one connected system. The project demonstrates practical full-stack development skills, database design/application, and role-based platform architecture.