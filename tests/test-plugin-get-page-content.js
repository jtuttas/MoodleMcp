const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

const pluginRoot = path.join(__dirname, '..', 'Plugin', 'local_aicoursecreator', 'local_aicoursecreator');

test('Moodle plugin registers a read-only page-content webservice', () => {
  const classFile = path.join(pluginRoot, 'classes', 'external', 'get_page_content.php');
  assert.ok(fs.existsSync(classFile), 'get_page_content.php must exist');

  const source = fs.readFileSync(classFile, 'utf8');
  assert.match(source, /'cmid'\s*=>\s*new external_value\(PARAM_INT/);
  assert.match(source, /get_coursemodule_from_id\('page'/);
  assert.match(source, /context_module::instance\(\$params\['cmid'\]\)/);
  assert.match(source, /require_capability\('mod\/page:view'/);
  assert.match(source, /'content'\s*=>\s*new external_value\(PARAM_RAW/);
  assert.doesNotMatch(source, /update_record|insert_record|delete_records|set_coursemodule_visible/);

  const services = fs.readFileSync(path.join(pluginRoot, 'db', 'services.php'), 'utf8');
  assert.match(services, /'local_aicoursecreator_get_page_content'\s*=>\s*\[/);
  assert.match(services, /'classname'\s*=>\s*'local_aicoursecreator\\external\\get_page_content'/);
  assert.match(services, /'type'\s*=>\s*'read'/);
  const serviceListMatches = services.match(/'local_aicoursecreator_get_page_content'/g) || [];
  assert.equal(serviceListMatches.length, 2, 'function must be defined and included in the service exactly once each');
});
