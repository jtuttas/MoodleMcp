const assert = require('node:assert/strict');
const http = require('node:http');
const path = require('node:path');
const { spawn } = require('node:child_process');
const test = require('node:test');

function createJsonLineReader(stream) {
  let buffer = '';
  const waiters = new Map();
  stream.setEncoding('utf8');
  stream.on('data', chunk => {
    buffer += chunk;
    const lines = buffer.split('\n');
    buffer = lines.pop();
    for (const line of lines) {
      if (!line.trim()) continue;
      const message = JSON.parse(line);
      const waiter = waiters.get(message.id);
      if (waiter) {
        waiters.delete(message.id);
        waiter.resolve(message);
      }
    }
  });
  return {
    waitFor(id) {
      return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
          waiters.delete(id);
          reject(new Error(`Timeout waiting for MCP response ${id}`));
        }, 5000);
        waiters.set(id, {
          resolve(message) {
            clearTimeout(timer);
            resolve(message);
          },
        });
      });
    },
  };
}

function request(child, reader, id, method, params = {}) {
  const response = reader.waitFor(id);
  child.stdin.write(`${JSON.stringify({ jsonrpc: '2.0', id, method, params })}\n`);
  return response;
}

test('moodle_get_label_content is read-only and returns raw label HTML', async t => {
  let received = null;
  const api = http.createServer((req, res) => {
    let raw = '';
    req.setEncoding('utf8');
    req.on('data', chunk => { raw += chunk; });
    req.on('end', () => {
      received = Object.fromEntries(new URLSearchParams(raw));
      res.writeHead(200, { 'content-type': 'application/json' });
      res.end(JSON.stringify({
        cmid: 315,
        courseid: 9,
        sectionnum: 1,
        instanceid: 80,
        name: 'L01',
        visible: 1,
        contentformat: 1,
        content: '<div id="ls1-l01">vollstaendig</div>',
      }));
    });
  });
  await new Promise(resolve => api.listen(0, '127.0.0.1', resolve));
  t.after(() => api.close());

  const { port } = api.address();
  const child = spawn(process.execPath, [path.join(__dirname, '..', 'moodle-mcp.js')], {
    env: { ...process.env, MOODLE_URL: `http://127.0.0.1:${port}`, MOODLE_TOKEN: 'test-token' },
    stdio: ['pipe', 'pipe', 'pipe'],
  });
  t.after(() => { child.stdin.end(); if (!child.killed) child.kill(); });
  const reader = createJsonLineReader(child.stdout);

  await request(child, reader, 1, 'initialize');
  const listed = await request(child, reader, 2, 'tools/list');
  const tool = listed.result.tools.find(item => item.name === 'moodle_get_label_content');
  assert.ok(tool, 'moodle_get_label_content must be advertised');
  assert.deepEqual(tool.inputSchema.required, ['cmid']);

  const called = await request(child, reader, 3, 'tools/call', {
    name: 'moodle_get_label_content', arguments: { cmid: 315 },
  });
  assert.equal(called.result.isError, undefined);
  const result = JSON.parse(called.result.content[0].text);
  assert.equal(result.cmid, 315);
  assert.equal(result.content, '<div id="ls1-l01">vollstaendig</div>');
  assert.equal(received.wsfunction, 'local_aicoursecreator_get_label_content');
  assert.equal(received.cmid, '315');
  assert.deepEqual(
    Object.keys(received).sort(),
    ['cmid', 'moodlewsrestformat', 'wsfunction', 'wstoken'].sort(),
    'read call must not send write-capable parameters',
  );
});
