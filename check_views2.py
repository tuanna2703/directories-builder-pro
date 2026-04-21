import os

plugin_dir = '.'
includes_dir = os.path.join(plugin_dir, 'includes')
modules_dir = os.path.join(plugin_dir, 'modules')

print("Checking View Violations IN CONTROLLERS / SERVICES...")

def grep_views(path):
    violations = []
    for root, d_names, f_names in os.walk(path):
        for f in f_names:
            if f.endswith('.php'):
                filepath = os.path.join(root, f)
                with open(filepath, 'r', encoding='utf-8') as file:
                    for i, line in enumerate(file, 1):
                        ls = line.strip()
                        if ('echo' in ls or '<html' in ls or '<div' in ls or '<span' in ls) and ('controllers' in filepath or 'services' in filepath):
                            violations.append((filepath, i, ls))
    return violations

v = grep_views(modules_dir)
for f, i, l in v:
    print(f"{f}:{i}: {l}")

v2 = grep_views(includes_dir)
for f, i, l in v2:
    print(f"{f}:{i}: {l}")

