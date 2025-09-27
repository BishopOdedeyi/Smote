# 📒 Smotes Open Source Note-Taking & File Management

Smotes is a **lightweight, secure, and extensible note-taking platform** built in PHP with MySQL.  
It goes beyond simple notes: you can **attach files (PDFs, images, docs)**, organize your thoughts, and manage your information seamlessly — all in one place.  

Unlike bloated CMS solutions (like WordPress with plugins), **Smotes is lean and purpose-built**:  
- Minimalist dashboard, distraction-free.  
- Fast and optimized for small to medium-scale usage.  
- Extensible — developers can fork and integrate it into their own workflows.  

## 🚀 Features

- 🔑 **User Authentication** – secure registration/login system.  
- 📝 **Rich Note Management** – create, edit, delete notes with timestamps.  
- 📎 **File Attachments** – upload PDFs, images, and other files.  
- 👀 **Clean Viewer** – view notes with inline previews of images and PDFs.  
- ✏️ **Edit & Delete Controls** – easily update or remove notes.  
- 🛡️ **User-Scoped Data** – each user only sees their own notes & files.  
- ⚡ **Lightweight** – no heavy frameworks, just pure PHP & MySQL.  

## 🌍 Why This Project Matters

This project was created to show that **great tools don’t have to be complex**.  
It’s **free and open source software (FOSS)** — meaning:  
- Developers can extend, customize, and integrate it.  
- Users can host their own note system without depending on big SaaS providers.  
- Communities can collaborate to make it better.  

Whether you’re a student, researcher, or professional team — Smotes can adapt to you.  

## 🛠️ Installation (Local Setup with XAMPP)

1. **Clone or Download this repository**
   ```bash
   git clone https://github.com/yourusername/smart-notes.git
```

Or [download ZIP](https://github.com/BishopOdedeyi/Smote/archive/refs/heads/main.zip).

2. **Move to your XAMPP htdocs folder**

   ```bash
   C:\xampp\htdocs\smart-notes
   ```

3. **Create the database**
   * Import the included SQL file:

     ```
     database.sql
     ```

4. **Configure database connection**

   * Open `config.php` and update:

     ```php
     $host = "localhost";
     $dbname = "day2";
     $username = "root"; // or your MySQL user
     $password = "";     // set your MySQL password if any
     ```

5. **Run the app**

   * Visit [http://localhost/smotes](http://localhost/smotes)
   * Register a new account and start adding notes 🚀

---

## 👨‍💻 Collaboration & Pull Requests

We welcome developers of all levels! Here’s how you can collaborate:

* **Fork** the repository.
* Create a new branch for your feature/fix:

  ```bash
  git checkout -b feature-amazing-idea
  ```
* Commit and push your changes.
* Open a **Pull Request** with details about your contribution.

All contributions (UI improvements, security patches, new features) are welcome 🎉

---

## 🔮 Future Prospects

Smotes is just the beginning. Here’s what we envision:

* 📱 **Mobile-Friendly UI** for easier use on phones & tablets.
* 🌐 **Collaboration Mode** (shared notes between users).
* 🔍 **Full-Text Search** across notes & attachments.
* 🏷️ **Tagging & Categorization** for better organization.
* ☁️ **Cloud Deploy Option** so teams can host it online.
* 🤖 **AI Integration (OCR/LLM)** to extract text from uploaded files automatically.

This makes Smotes a future-ready platform for **personal productivity and team collaboration**.

---

## 📜 License

This project is released under the **MIT License**.
You are free to use, modify, and distribute it — commercially or personally — as long as you keep the license notice.

---

## 💡 Final Words

Smotes proves that:

> *Simple tools can make a big difference.*

Try it, fork it, improve it.
Whether you’re a developer building features, or a user hosting your own notes, **this project belongs to you**.

👉 Start now: clone the repo, import the DB, and create your first note!




