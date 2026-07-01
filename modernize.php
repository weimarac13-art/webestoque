<?php
$files = [
    'movimentacoes.php',
    'produtos.php',
    'agencias.php',
    'tecnicos.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // 1. Modal Header
        $content = str_replace('bg-gray-100 px-6 py-4 border-b border-gray-200', 'px-8 pt-8 pb-2', $content);
        
        // 2. Modal Body/Form padding
        $content = str_replace('class="p-6 space-y-4', 'class="px-8 pb-8 pt-2 space-y-5', $content);
        $content = str_replace('class="p-6 space-y-6', 'class="px-8 pb-8 pt-2 space-y-6', $content);
        
        // 3. Inputs standard
        $oldInput1 = 'border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb]/30';
        $newInput = 'border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all';
        $content = str_replace($oldInput1, $newInput, $content);
        
        $oldInput2 = 'border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500/30';
        $content = str_replace($oldInput2, $newInput, $content);

        $oldInput3 = 'border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-gray-500 focus:ring-1';
        $content = str_replace($oldInput3, $newInput, $content);

        $oldInput4 = 'border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-gray-500';
        $content = str_replace($oldInput4, $newInput, $content);
        
        $oldInput5 = 'border border-gray-200 px-4 py-2.5 text-sm font-semibold outline-none focus:border-gray-500 focus:ring-1 bg-gray-50';
        $content = str_replace($oldInput5, $newInput . ' font-semibold', $content);
        
        // 4. Cancel Button
        $oldCancel = 'border border-gray-200 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50';
        $newCancel = 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 py-3 text-sm font-bold hover:bg-slate-50 hover:text-slate-900';
        $content = str_replace($oldCancel, $newCancel, $content);

        // 5. Title changes to make it look nicer
        $content = preg_replace('/<h3 class="text-lg font-bold text-gray-900"/', '<h3 class="text-2xl font-black text-gray-900 tracking-tight"', $content);
        
        // 6. Label changes
        $content = str_replace('text-xs font-bold text-gray-500 uppercase', 'text-[11px] font-black text-slate-400 uppercase tracking-wider', $content);

        file_put_contents($file, $content);
        echo "Modernized $file\n";
    }
}
echo "Done.";
