# FileActions_BackendDemo

## About the Project

FileActions_BackendDemo is a demonstration project showcasing a full-stack application with a primary focus on backend development using PHP and the Symfony framework. It highlights best practices, clean architecture, and integration with modern tools and technologies. The application, "File Actions Demo", allows users to perform various actions on files, such as resizing, converting, and compressing images.

### Key Features

*   **Image Resizing:** Easily resize your images to specific dimensions or by a percentage.
*   **Image Format Conversion:** Convert images between popular formats like PNG, JPG, and WEBP.
*   **Image Compression:** Reduce image file sizes with adjustable quality settings to optimize for web or storage.
*   **User-Friendly Interface:** Modern, intuitive interface with drag-and-drop file uploads powered by FilePond.
*   **Real-time Feedback:** Clear notifications for successful operations or errors using SweetAlert2.
*   **RESTful API:** Well-defined API endpoints for all file operations, enabling easy integration with other services or frontends.

### Technical Highlights

*   **Backend:** Symfony 6, PHP 8.x
    *   RESTful API design.
    *   Service-oriented architecture for modularity and maintainability.
    *   Robust error handling and input validation.
    *   Doctrine ORM for database interactions (if applicable in future extensions).
*   **Frontend:** TypeScript, Twig, Vite, FilePond, SweetAlert2
    *   Efficient asset management and fast development builds with Vite.
    *   Twig templating for server-side rendering of initial views.
    *   TypeScript for type-safe JavaScript development.
    *   Interactive file uploads with FilePond.
    *   User-friendly notifications with SweetAlert2.
*   **Development & Deployment:**
    *   Containerized environment using Podman/Docker Compose for consistent and easy setup (see `container/docker-compose.yml`).
    *   PHPUnit for backend testing (setup available in `phpunit.xml.dist`).
    *   Clear project structure following Symfony best practices.

### Technologies Used
*   **PHP 8.x** (Symfony 6 Framework)
*   **Twig** (Templating Engine)
*   **TypeScript** (Frontend Logic)
*   **Vite** (Frontend Asset Management & Build Tool)
*   **FilePond** (File Upload Library)
*   **SweetAlert2** (Notification Library)
*   **Podman / Docker Compose** (Containerization)

## Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites

*   Podman or Docker with Docker Compose installed.
*   Git.

### Installation & Running the Container

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your_username/FileActions_BackendDemo.git
    cd FileActions_BackendDemo
    ```
2.  **Navigate to the `container` directory:**
    ```bash
    cd container
    ```
3.  **Start the application using Podman Compose:**
    ```bash
    podman-compose up -d
    ```
    Alternatively, if you prefer Docker Compose, you can use:
    ```bash
    docker-compose up -d
    ```
4.  **Access the application:**
    Open your browser and navigate to `http://localhost:40055` (as per your `docker-compose.yml`).

## Available Actions

Once the application is running, you can perform the following actions from the "File Actions Demo" main page:

*   **Resize Images:** Go to the "Resize" section.
*   **Convert Image Formats:** Go to the "Convert Extension" section.
*   **Compress Images:** Go to the "Compress" section.

## Purpose

This project serves as a comprehensive demo to showcase backend development skills using Symfony, with a strong emphasis on clean code, modularity, and integration with modern frontend and development tools. It is designed for learning, experimentation, and demonstrating proficiency in building full-stack web applications.

## Future Enhancements (Examples)

*   Adding more image manipulation features (e.g., filters, cropping, watermarking).
*   User authentication and authorization.
*   Support for other file types (e.g., documents, videos).
*   Implementing a job queue for long-running file processing tasks.
