# ⚙️ FantumCloudBack

FantumCloudBack is the core backend service of the **Fantum Cloud Suite**.  
It handles workspaces, files, permissions, collaboration, and real-time updates.  
Authentication is delegated to **FantumID**, allowing a clean separation between identity and resource management.

*Made with Symfony ❤️*

## 🚀 Features

### 🔐 Authentication & User Access
- Access tokens validated directly through **FantumID**
- Secure user/session resolution middleware
- Unified permissions extraction for all routes

### 🗂️ Workspaces
- Workspace creation, update, deletion
- Workspace ownership and transfer logic
- Invitation system to add users to a workspace
- Automatic notification workflow

### 👥 Roles & Permissions
- Workspace-level role management  
- Default permissions per role
- Ordered role hierarchy
- Users can have **multiple roles** per workspace
- Custom object-level permissions for:
  - Files
  - Folders

- Permissions: `Read`, `Write`, `Trash`, `Delete`, `Edit permissions`, `Manage members`

### 📁 File & Folder System
FantumCloudBack manages a complete virtual filesystem:
- Upload files
- Download files
- Move files/folders
- Rename files/folders
- Delete files/folders
- Read metadata
- Manage per-role permissions on each file or folder
Every action is permission-gated through the workspace’s role system.

### 📝 Datatables (special file type)
FantumCloudBack includes a special file called **datatable**:

- Create and manage structured data collections (similar to Notion databases)
- Field types (text, number, select, multiselect, relation, etc.)
- Custom views with filters and sorting
- Entry creation, update, deletion

*Datatables are special types of files so they still have files features*

### 🖇️ OnlyOffice Integration
Designed for real-time document collaboration:
- Dedicated API routes for OnlyOffice callbacks get and update

*Onlyoffice sessions are created by the frontend*

### 🔄 Real-Time Updates (Mercure SSE)
FantumCloudBack publishes all key operations to a [Mercure](https://mercure.rocks/) hub:

Events include:
- Workspace updates
- Permission changes
- File/folder operations
- Datatable modifications

This enables:
- Live UI updates
- Multi-user collaboration
