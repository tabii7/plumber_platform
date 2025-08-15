@php
  $types = ['text'=>'Text','buttons'=>'Buttons','list'=>'List','collect_text'=>'Collect Text','dispatch'=>'Dispatch'];
  $opts  = old('options', ['id'=>[], 'label'=>[]]);
  $next  = old('next', ['keys'=>[], 'values'=>[]]);

  $existingOptions = $node->options_json ?? [];
  if (!old('_token') && !empty($existingOptions)) {
      $opts = ['id'=>[], 'label'=>[]];
      foreach ($existingOptions as $o) {
          $opts['id'][]    = $o['id']    ?? '';
          $opts['label'][] = $o['label'] ?? '';
      }
  }

  $existingMap = $node->next_map_json ?? [];
  if (!old('_token') && !empty($existingMap)) {
      $next = ['keys'=>[], 'values'=>[]];
      foreach ($existingMap as $k=>$v) {
          $next['keys'][]   = $k;
          $next['values'][] = $v;
      }
  }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block text-sm font-medium text-gray-700">Code (unique in flow)</label>
    <input type="text" name="code" value="{{ old('code', $node->code) }}" class="mt-1 w-full border rounded p-2">
    @error('code') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Type</label>
    <select name="type" id="nodeType" class="mt-1 w-full border rounded p-2">
      @foreach($types as $k=>$v)
        <option value="{{ $k }}" @selected(old('type',$node->type)===$k)>{{ $v }}</option>
      @endforeach
    </select>
    @error('type') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Title (optional)</label>
    <input type="text" name="title" value="{{ old('title', $node->title) }}" class="mt-1 w-full border rounded p-2">
    @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-gray-700">Body</label>
    <textarea name="body" rows="5" class="mt-1 w-full border rounded p-2" placeholder="Message text shown to the user">{{ old('body', $node->body) }}</textarea>
    @error('body') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Footer (optional)</label>
    <input type="text" name="footer" value="{{ old('footer', $node->footer) }}" class="mt-1 w-full border rounded p-2">
    @error('footer') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Sort</label>
    <input type="number" name="sort" value="{{ old('sort', $node->sort ?? 10) }}" class="mt-1 w-full border rounded p-2">
    @error('sort') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
  </div>
</div>

{{-- Options (for buttons/list) --}}
<div id="optionsBlock" class="mt-6">
  <div class="flex items-center justify-between">
    <h3 class="text-sm font-semibold text-gray-900">Options (for buttons/list)</h3>
    <button type="button" onclick="addOptionRow()" class="text-sm text-blue-600">+ Add option</button>
  </div>
  <div id="optionsRows" class="mt-2 space-y-2">
    @php $count = max(count($opts['id'] ?? []), 1); @endphp
    @for($i=0; $i<$count; $i++)
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <input name="options[id][]" value="{{ $opts['id'][$i] ?? '' }}" class="border rounded p-2" placeholder="id (key)">
        <input name="options[label][]" value="{{ $opts['label'][$i] ?? '' }}" class="border rounded p-2" placeholder="Label shown to user">
      </div>
    @endfor
  </div>
</div>

{{-- Next map --}}
<div class="mt-6">
  <div class="flex items-center justify-between">
    <h3 class="text-sm font-semibold text-gray-900">Next node routing</h3>
    <button type="button" onclick="addNextRow()" class="text-sm text-blue-600">+ Add rule</button>
  </div>
  <p class="text-xs text-gray-500 mt-1">
    Map user replies to next node code. For <b>buttons/list</b>, keys usually match option <b>id</b> or the <b>number</b> (1,2,3...).<br>
    For <b>collect_text</b>, add a rule with key <code>next</code>.
  </p>

  <div id="nextRows" class="mt-2 space-y-2">
    @php $ncount = max(count($next['keys'] ?? []), 1); @endphp
    @for($i=0; $i<$ncount; $i++)
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <input name="next[keys][]" value="{{ $next['keys'][$i] ?? '' }}" class="border rounded p-2" placeholder="reply key (e.g., yes, 1, available, next)">
        <input name="next[values][]" value="{{ $next['values'][$i] ?? '' }}" class="border rounded p-2" placeholder="next node code">
      </div>
    @endfor
  </div>
</div>

<script>
  const typeSelect = document.getElementById('nodeType');
  const optionsBlock = document.getElementById('optionsBlock');

  function toggleOptions() {
    const t = typeSelect.value;
    optionsBlock.style.display = (t === 'buttons' || t === 'list') ? 'block' : 'none';
  }
  toggleOptions();
  typeSelect.addEventListener('change', toggleOptions);

  function addOptionRow() {
    const wrap = document.getElementById('optionsRows');
    const div = document.createElement('div');
    div.className = 'grid grid-cols-1 md:grid-cols-2 gap-2';
    div.innerHTML = `
      <input name="options[id][]" class="border rounded p-2" placeholder="id (key)">
      <input name="options[label][]" class="border rounded p-2" placeholder="Label shown to user">
    `;
    wrap.appendChild(div);
  }

  function addNextRow() {
    const wrap = document.getElementById('nextRows');
    const div = document.createElement('div');
    div.className = 'grid grid-cols-1 md:grid-cols-2 gap-2';
    div.innerHTML = `
      <input name="next[keys][]" class="border rounded p-2" placeholder="reply key (e.g., yes, 1, available, next)">
      <input name="next[values][]" class="border rounded p-2" placeholder="next node code">
    `;
    wrap.appendChild(div);
  }
</script>
