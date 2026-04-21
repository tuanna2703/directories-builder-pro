import os
import re

plugin_dir = '.'
prd_path = os.path.join(plugin_dir, 'prd.md')
structure_path = os.path.join(plugin_dir, 'structure.md')

# Helper functions
def get_files(path, ext='.php'):
    files = []
    for root, d_names, f_names in os.walk(path):
        for f in f_names:
            if f.endswith(ext):
                files.append(os.path.join(root, f))
    return files

def find_file(filename):
    for root, dirs, files in os.walk(plugin_dir):
        if filename in files:
            return os.path.join(root, filename)
    return None

def check_file_exists(rel_path):
    return "✅ exists" if os.path.exists(os.path.join(plugin_dir, rel_path)) else "❌ none"

modules = ['business', 'claims', 'form', 'maps', 'reviews', 'search', 'template']
core_managers = ['module-manager.php', 'asset-manager.php', 'ajax-manager.php', 'template-manager.php', 'form-manager.php']
includes_repositories = ['business-repository.php', 'review-repository.php', 'claim-repository.php']

print("\n--- Section 1.7 Missing Components (sample) ---\n")
print("| Required Component | Required By | Missing File | Priority |")
print("|--------------------|-------------|--------------|----------|")

if not os.path.exists(os.path.join(plugin_dir, 'modules/claims/repositories/claim-repository.php')) and not os.path.exists(os.path.join(plugin_dir, 'includes/repositories/claim-repository.php')):
    print("| Claim Repository | prd.md §7, claims module | modules/claims/repositories/claim-repository.php | High |")

if not os.path.exists(os.path.join(plugin_dir, 'modules/maps/controllers/map-controller.php')):
    print("| Maps Controller | structure.md | modules/maps/controllers/map-controller.php | Medium |")

if not os.path.exists(os.path.join(plugin_dir, 'modules/claims/models/claim.php')):
    print("| Claim Model | prd.md §7, claims module | modules/claims/models/claim.php | High |")

if not os.path.exists(os.path.join(plugin_dir, 'modules/search/models/search-result.php')):
    print("| Search Result Model | prompt-audit.md | modules/search/models/search-result.php | High |")

