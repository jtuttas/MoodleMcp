const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

const pluginRoot = path.join(__dirname, '..', 'Plugin', 'local_aicoursecreator', 'local_aicoursecreator');

test('Moodle plugin registers a read-only label-content webservice', () => {
  const classFile = path.join(pluginRoot, 'classes', 'external', 'get_label_content.php');
  assert.ok(fs.existsSync(classFile), 'get_label_content.php must exist');

  const source = fs.readFileSync(classFile, 'utf8');
  assert.match(source, /get_coursemodule_from_id\('label'/);
  assert.match(source, /context_module::instance\(\$params\['cmid'\]\)/);
  assert.match(source, /require_capability\('mod\/label:view'/);
  assert.match(source, /'label'[\s\S]*'id, course, name, intro, introformat'/);
  assert.match(source, /'content'\s*=>\s*\$label->intro/);
  assert.match(source, /'content'\s*=>\s*new external_value\(PARAM_RAW/);
  assert.doesNotMatch(source, /update_record|insert_record|delete_records|set_field|set_coursemodule_visible/);

  const services = fs.readFileSync(path.join(pluginRoot, 'db', 'services.php'), 'utf8');
  assert.match(services, /'local_aicoursecreator_get_label_content'\s*=>\s*\[/);
  assert.match(services, /'classname'\s*=>\s*'local_aicoursecreator\\external\\get_label_content'/);
  const matches = services.match(/'local_aicoursecreator_get_label_content'/g) || [];
  assert.equal(matches.length, 2, 'function must be defined and included exactly once');
});
