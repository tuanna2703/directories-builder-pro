import os
import re

plugin_dir = '/Volumes/DATA/Workspace/Development/MAMP/htdocs/wordpress-plugins/wp-content/plugins/directories-builder-pro'

def get_files(path, ext='.php'):
    files = []
    for root, d_names, f_names in os.walk(path):
        for f in f_names:
            if f.endswith(ext):
                files.append(os.path.join(root, f))
    return files

modules_dir = os.path.join(plugin_dir, 'modules')
modules = [d for d in os.listdir(modules_dir) if os.path.isdir(os.path.join(modules_dir, d))]

print("Modules found:", modules)

def check_file_exists(path):
    return "✅ exists" if os.path.exists(path) else "❌ none"
    
def check_dir_files(path):
    if not os.path.exists(path): return "❌ none"
    count = len(get_files(path))
    if count == 0: return "❌ none"
    return f"✅ {count} file" + ("s" if count > 1 else "")

print("\n--- Section 1.1 ---\n")
print("| Module | module.php | Controllers | Services | Models | Repositories | AJAX | Templates |")
print("|---|---|---|---|---|---|---|---|")
for module in modules:
    m_dir = os.path.join(modules_dir, module)
    m_php = check_file_exists(os.path.join(m_dir, f"{module}-module.php")) # Wait, they use "-module.php"?
    # The prompt actually checks for `module.php` so let's see what exists actually
    module_php_path = os.path.join(m_dir, 'module.php')
    if not os.path.exists(module_php_path):
        module_php_path = os.path.join(m_dir, f"{module}-module.php")
    
    m_php = check_file_exists(module_php_path)
    # Notice we use actual paths for subdirs based on typical structure
    controllers = check_dir_files(os.path.join(m_dir, 'controllers'))
    services = check_dir_files(os.path.join(m_dir, 'services'))
    models = check_dir_files(os.path.join(m_dir, 'models'))
    repositories = check_dir_files(os.path.join(m_dir, 'repositories'))
    ajax = check_dir_files(os.path.join(m_dir, 'ajax'))
    templates = check_dir_files(os.path.join(m_dir, 'templates'))
    
    print(f"| {module} | {m_php} | {controllers} | {services} | {models} | {repositories} | {ajax} | {templates} |")

