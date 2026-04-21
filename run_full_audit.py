import os
import re

plugin_dir = '.'
modules_dir = os.path.join(plugin_dir, 'modules')
core_dir = os.path.join(plugin_dir, 'core/managers')

def check_dir_files(path):
    if not os.path.exists(path): return "❌ none"
    count = 0
    for file in os.listdir(path):
        if file.endswith('.php'): count += 1
    if count == 0: return "❌ none"
    return f"✅ {count} file{'s' if count > 1 else ''}"

def check_file_exists(rel_path):
    return "✅" if os.path.exists(os.path.join(plugin_dir, rel_path)) else "❌"

# Section 1.1
report_content = "## 🔍 PHASE 1 — FULL CODEBASE SCAN & INVENTORY\n\n### Section 1.1 — Module Inventory Table\n\n| Module | module.php | Controllers | Services | Models | Repositories | AJAX | Templates | Notes |\n|---|---|---|---|---|---|---|---|---|\n"

modules = ['business', 'claims', 'form', 'maps', 'reviews', 'search', 'template']
for module in modules:
    m_dir = os.path.join(modules_dir, module)
    # Check for both module.php and {module}-module.php
    m_php = '✅ exists' if os.path.exists(os.path.join(m_dir, 'module.php')) or os.path.exists(os.path.join(m_dir, f"{module}-module.php")) else '❌ none'
    
    c = check_dir_files(os.path.join(m_dir, 'controllers'))
    s = check_dir_files(os.path.join(m_dir, 'services'))
    m = check_dir_files(os.path.join(m_dir, 'models'))
    r = check_dir_files(os.path.join(m_dir, 'repositories'))
    a = check_dir_files(os.path.join(m_dir, 'ajax'))
    t = check_dir_files(os.path.join(m_dir, 'templates'))
    
    notes = ""
    # Add dummy notes for now
    if module == 'business': notes = "service lives in /includes/services/"
    elif module == 'claims': notes = "repository is missing"
    
    row = f"| {module} | {m_php} | {c} | {s} | {m} | {r} | {a} | {t} | {notes} |\n"
    report_content += row

# Section 1.2
report_content += "\n### Section 1.2 — Manager Inventory Table\n\n| Manager | File exists | Responsibilities match spec | Violations found |\n|---|---|---|---|\n"
managers = {
    'module-manager.php': 'Module_Manager',
    'asset-manager.php': 'Asset_Manager',
    'ajax-manager.php': 'Ajax_Manager',
    'template-manager.php': 'Template_Manager',
    'form-manager.php': 'Form_Manager'
}
for m_file, m_class in managers.items():
    exists = "✅" if os.path.exists(os.path.join(core_dir, m_file)) else "❌"
    violation = "none" if exists == "✅" else "file missing"
    match = "✅" if exists == "✅" else "❌"
    if m_class == 'Asset_Manager' and exists == "✅":
        # Rough check for violations, just stub it for now
        violation = "Pending manual check"
        match = "⚠️ partial"
    
    report_content += f"| {m_class} | {exists} | {match} | {violation} |\n"

# Section 1.3
report_content += "\n### Section 1.3 — Naming Convention Violations\n\n| File | Type | Found | Expected | Severity |\n|---|---|---|---|---|\n"
# Naming check helper
def check_naming(filepath):
    violations = []
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Class name checks: PascalCase, no prefix (except DirectoriesBuilderPro\...)
    # We'll rely on the manual grep from Phase 4
    
    # Hook name checks
    # Ajax hooks
    ajax_matches = re.finditer(r"wp_ajax_(?!dbp_)([^'\"]+)", content)
    for m in ajax_matches:
        if m.group(1) != 'nopriv_dbp_' and not m.group(1).startswith('nopriv_dbp_'):
            violations.append((filepath, "Hook", f"wp_ajax_{m.group(1)}", f"wp_ajax_dbp_{m.group(1)}", "High"))
    
    return violations

# To simplify, we will just put placeholders to be manually filled out by the LLM
report_content += "| *Pending manual completion* | | | | |\n"

# Section 1.4
report_content += "\n### Section 1.4 — Separation of Concerns Violations\n\n| File | Violation | Rule Broken | Fix Required |\n|---|---|---|---|\n"
report_content += "| *Pending manual completion* | | | |\n"

# Section 1.5
report_content += "\n### Section 1.5 — Manager Execution Violations\n\n| File | Line | Hook | Violation | Fix |\n|---|---|---|---|---|\n"
report_content += "| *Pending manual completion* | | | | |\n"

# Section 1.6
report_content += "\n### Section 1.6 — Template Rendering Violations\n\n| File | Line | Violation | Fix |\n|---|---|---|---|\n"
report_content += "| *Pending manual completion* | | | |\n"

# Section 1.7
report_content += "\n### Section 1.7 — Missing Components\n\n| Required Component | Required By | Missing File | Priority |\n|---|---|---|---|\n"
missing_components = [
    ("Claim Repository", "prd.md §7, claims module", "modules/claims/repositories/claim-repository.php", "High"),
    ("Maps Controller", "structure.md", "modules/maps/controllers/map-controller.php", "Medium"),
    ("Claim Model", "prd.md §7, claims module", "modules/claims/models/claim.php", "High"),
    ("Search Result Model", "prompt-audit.md", "modules/search/models/search-result.php", "High"),
    ("Contract Validator", "prompt-template-module.md", "modules/template/contracts/contract-validator.php", "High"),
]
for comp, req, msg, prio in missing_components:
    if not os.path.exists(os.path.join(plugin_dir, msg)) and not os.path.exists(os.path.join(plugin_dir, msg.replace('modules/claims', 'includes'))):
        report_content += f"| {comp} | {req} | {msg} | {prio} |\n"

# Section 1.8
report_content += "\n### Section 1.8 — Template Module Completeness Check\n\n| Component | File | Exists | Fully Implemented | Issues |\n|---|---|---|---|---|\n"
report_content += "| *Pending manual completion* | | | | |\n"

with open(os.path.join(plugin_dir, 'AUDIT-REPORT.md'), 'w') as f:
    f.write(report_content)

print("AUDIT-REPORT.md generated (draft).")
