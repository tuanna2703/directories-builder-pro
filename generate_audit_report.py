import os

plugin_dir = '/Volumes/DATA/Workspace/Development/MAMP/htdocs/wordpress-plugins/wp-content/plugins/directories-builder-pro'

def get_files(path, ext='.php'):
    files = []
    for root, d_names, f_names in os.walk(path):
        for f in f_names:
            if f.endswith(ext):
                files.append(os.path.join(root, f))
    return files

core_dir = os.path.join(plugin_dir, 'core/managers')
managers = ['module-manager.php', 'asset-manager.php', 'ajax-manager.php', 'template-manager.php', 'form-manager.php']

print("\n--- Section 1.2 ---\n")
print("| Manager            | File exists | Responsibilities match spec | Violations found |")
print("|--------------------|-------------|----------------------------|------------------|")
for manager in managers:
    m_path = os.path.join(core_dir, manager)
    exists = "✅" if os.path.exists(m_path) else "❌"
    
    match = "⚠️ pending manual check"
    violations = "pending manual check"
    if exists == "❌":
        match = "❌"
        violations = "file missing"
        
    print(f"| {manager} | {exists} | {match} | {violations} |")

