import os

plugin_dir = '.'
manager_dir = os.path.join(plugin_dir, 'modules')

print("Checking Manager Violations...")

def grep_hooks(path):
    violations = []
    for root, d_names, f_names in os.walk(path):
        for f in f_names:
            if f.endswith('.php'):
                filepath = os.path.join(root, f)
                with open(filepath, 'r', encoding='utf-8') as file:
                    for i, line in enumerate(file, 1):
                        if 'add_action' in line or 'add_filter' in line or 'register_rest_route' in line or 'add_menu_page' in line:
                            violations.append((filepath, i, line.strip()))
    return violations

v = grep_hooks(manager_dir)
for f, i, l in v:
    print(f"{f}:{i}: {l}")

