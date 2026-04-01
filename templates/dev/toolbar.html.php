<?php
use Core\Service\GzipCompressor;
use Dev\DevModeManager;
use Dev\PerformanceProfiler;

// В конце body, перед Dev toolbar:
if (DevModeManager::isEnabled()) {
    $data = DevModeManager::collectToolbarData();

    if (GzipCompressor::isEnabled()) {
        // Получаем размер вывода (примерный)
        $output = ob_get_contents();
        $originalSize = strlen($output);

        // Для сжатого контента получаем сжатый размер
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && 
            stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            // Создаем временную сжатую версию для расчета
            $compressed = GzipCompressor::compress($output);
            if ($compressed !== false) {
                $compressedSize = strlen($compressed);

                // Логируем статистику
                $contentType = headers_list();
                $contentType = array_filter($contentType, function($header) {
                    return stripos($header, 'Content-Type:') === 0;
                });
                $contentType = reset($contentType) ?: 'text/html';

                DevModeManager::logGzipStats(
                    $_SERVER['REQUEST_URI'] ?? '/',
                    $originalSize,
                    $compressedSize,
                    $contentType
                );
            }
        }
    }
}
?>

<div style="position: fixed; bottom: 0; right: 0; 
    color: #e2e8f0; padding: 8px 12px; font-family: 'Monaco', 'Menlo', monospace;
      font-size: 11px; z-index: 9999; display: flex; 
      justify-content: space-between; align-items: center; opacity: 0.95;">
    <button onclick="toggleToolbar()"
            style="width: 28px; background: #718096; border: none; color: white; padding: 3px 8px; 
                   border-radius: 3px; font-size: 10px; cursor: pointer;">
        🛠
    </button>
</div>

<div id="dev-toolbar" style="position: fixed; bottom: 0; left: 0; right: 0; 
      background: #2d3748; color: #e2e8f0; padding: 8px 12px; font-family: 'Monaco', 'Menlo', monospace;
      font-size: 11px; z-index: 9999; border-top: 1px solid #4a5568; display: flex; 
      justify-content: space-between; align-items: center; opacity: 0.95;">
    
    <!-- Left section: Basic metrics -->
    <div style="display: flex; gap: 15px; align-items: center;">
        <span title="Execution time">
            🕒 <?= round($data['performance']['execution_time'] * 1000, 0) ?>ms
        </span>
        <span title="Memory usage">
            💾 <?= round($data['performance']['memory_usage'] / 1024 / 1024, 1) ?>MB
        </span>
        <span title="Peak memory">
            📈 <?= round($data['performance']['memory_peak'] / 1024 / 1024, 1) ?>MB
        </span>
        <span title="Database queries">
            🗄️ <?= $data['performance']['query_count'] ?>q
        </span>
        <span title="Query time">
            ⚡ <?= $data['performance']['query_time'] ?>ms
        </span>
    </div>
    
    <!-- Center section: Environment & Cache info -->
    <div style="display: flex; gap: 10px; align-items: center;">
        <span style="padding: 2px 6px; border-radius: 3px; font-size: 10px;
              background: <?= $data['environment']['mode'] === 'production' ? '#38a169' : 
                          ($data['environment']['mode'] === 'dev' ? '#d69e2e' : '#3182ce') ?>;">
            <?= strtoupper($data['environment']['mode']) ?>
        </span>
        
        <!-- OpCache indicator -->
        <?php if ($data['performance']['opcache_enabled']): ?>
            <span title="OpCache Hit Rate: <?= $data['performance']['opcache_hit_rate'] ?>%"
                  style="display: flex; align-items: center; gap: 3px;">
                <span style="color: <?= $data['performance']['opcache_hit_rate'] > 90 ? '#68d391' : 
                                      ($data['performance']['opcache_hit_rate'] > 70 ? '#d69e2e' : '#fc8181') ?>">
                    🚀
                </span>
                <span><?= $data['performance']['opcache_cached_scripts'] ?> files</span>
                <span style="color: #a0aec0; font-size: 10px;">
                    (<?= $data['performance']['opcache_hit_rate'] ?>%)
                </span>
            </span>
        <?php else: ?>
            <span title="OpCache disabled" style="color: #fc8181;">
                🚫 OpCache
            </span>
        <?php endif; ?>
        
        <!-- JIT indicator -->
        <?php if ($data['performance']['jit_enabled']): ?>
            <span title="JIT: <?= $data['performance']['jit_mode'] ?>"
                  style="display: flex; align-items: center; gap: 3px;">
                <span style="color: #68d391;">⚡</span>
                <span><?= ucfirst($data['performance']['jit_mode']) ?></span>
                <?php if ($data['performance']['jit_buffer_usage'] > 0): ?>
                    <span style="color: #a0aec0; font-size: 10px;">
                        (<?= $data['performance']['jit_buffer_usage'] ?>%)
                    </span>
                <?php endif; ?>
            </span>
        <?php else: ?>
            <span title="JIT disabled" style="color: #a0aec0;">
                ⚡ JIT
            </span>
        <?php endif; ?>

        <?php if ($data['performance']['gzip_enabled']): ?>
            <span title="Gzip Compression Level: <?= $data['performance']['gzip_compression_level'] ?>"
                  style="display: flex; align-items: center; gap: 3px;">
                <span style="color: 
                    <?= $data['performance']['gzip_compression_ratio'] > 75 ? '#68d391' : 
                       ($data['performance']['gzip_compression_ratio'] > 60 ? '#d69e2e' : '#fc8181') ?>">
                    📦
                </span>
                <span>Gzip</span>
                <span style="color: #a0aec0; font-size: 10px;">
                    (<?= $data['performance']['gzip_compression_ratio'] ?>%)
                </span>
            </span>
        <?php else: ?>
            <span title="Gzip disabled" style="color: #a0aec0;">
                📦 Gzip
            </span>
        <?php endif; ?>
    </div>
    
    <!-- Right section: Controls -->
    <div style="display: flex; gap: 8px; align-items: center;">
        <button onclick="toggleDevPanel()" 
                style="background: #4a5568; border: none; color: white; padding: 3px 8px; 
                       border-radius: 3px; font-size: 10px; cursor: pointer;">
            📊 Details
        </button>
        <button onclick="clearDevCache()"
                style="background: #805ad5; border: none; color: white; padding: 3px 8px; 
                       border-radius: 3px; font-size: 10px; cursor: pointer;">
            🗑️ Clear Cache
        </button>
        <button onclick="resetOpCache()"
                style="background: #3182ce; border: none; color: white; padding: 3px 8px; 
                       border-radius: 3px; font-size: 10px; cursor: pointer;"
                title="Reset OpCache & JIT">
            🔄 Reset Cache
        </button>
        <button onclick="toggleToolbar()"
                style="width: 28px; background: #718096; border: none; color: white; padding: 3px 8px; 
                       border-radius: 3px; font-size: 10px; cursor: pointer;">
            ❌
        </button>
    </div>
</div>

<!-- Details Panel -->
<div id="dev-panel" style="display: none; position: fixed; bottom: 40px; right: 20px; 
      width: 500px; max-height: 600px; background: #1a202c; color: #e2e8f0; 
      border: 1px solid #4a5568; border-radius: 6px; padding: 15px; z-index: 9998;
      overflow-y: auto; font-size: 11px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <h4 style="margin: 0; color: #81e6d9;">Development Panel</h4>
        <button onclick="closeDevPanel()" style="background: none; border: none; color: #a0aec0; cursor: pointer;">✕</button>
    </div>
    
    <!-- Tabs -->
    <div style="display: flex; gap: 5px; margin-bottom: 15px; border-bottom: 1px solid #4a5568; flex-wrap: wrap;">
        <button class="dev-tab active" onclick="showTab('performance')">Performance</button>
        <button class="dev-tab" onclick="showTab('opcache')">OpCache</button>
        <button class="dev-tab" onclick="showTab('jit')">JIT</button>
        <button class="dev-tab" onclick="showTab('gzip')">Gzip</button>
        <button class="dev-tab" onclick="showTab('queries')">Queries</button>
        <button class="dev-tab" onclick="showTab('request')">Request</button>
        <button class="dev-tab" onclick="showTab('system')">System</button>
        <button class="dev-tab" onclick="showTab('assets')">Assets</button>
    </div>
    
    <!-- Performance Tab -->
    <div id="tab-performance" class="dev-tab-content">
        <h5>Performance Metrics</h5>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div style="background: #2d3748; padding: 8px; border-radius: 4px;">
                <div style="font-size: 10px; color: #a0aec0;">Execution Time</div>
                <div style="font-size: 14px; color: #68d391;">
                    <?= round($data['performance']['execution_time'] * 1000, 0) ?>ms
                </div>
            </div>
            <div style="background: #2d3748; padding: 8px; border-radius: 4px;">
                <div style="font-size: 10px; color: #a0aec0;">Memory Usage</div>
                <div style="font-size: 14px; color: #68d391;">
                    <?= round($data['performance']['memory_usage'] / 1024 / 1024, 1) ?>MB
                </div>
            </div>
            <div style="background: #2d3748; padding: 8px; border-radius: 4px;">
                <div style="font-size: 10px; color: #a0aec0;">OpCache Hit Rate</div>
                <div style="font-size: 14px; color: 
                    <?= $data['performance']['opcache_hit_rate'] > 90 ? '#68d391' : 
                       ($data['performance']['opcache_hit_rate'] > 70 ? '#d69e2e' : '#fc8181') ?>">
                    <?= $data['performance']['opcache_hit_rate'] ?>%
                </div>
            </div>
            <div style="background: #2d3748; padding: 8px; border-radius: 4px;">
                <div style="font-size: 10px; color: #a0aec0;">JIT Status</div>
                <div style="font-size: 14px; color: <?= $data['performance']['jit_enabled'] ? '#68d391' : '#fc8181' ?>">
                    <?= $data['performance']['jit_enabled'] ? 'Enabled' : 'Disabled' ?>
                </div>
            </div>
        </div>
        
        <h5 style="margin-top: 15px;">Recommendations</h5>
        <?php if (!empty($data['recommendations'])): ?>
            <ul style="margin: 5px 0; padding-left: 20px; max-height: 150px; overflow-y: auto;">
                <?php foreach ($data['recommendations'] as $rec): ?>
                    <li style="margin-bottom: 3px; padding: 3px 0; border-bottom: 1px solid #2d3748;">
                        <?= htmlspecialchars($rec) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div style="color: #a0aec0; text-align: center; padding: 10px; background: #2d3748; border-radius: 4px;">
                No recommendations
            </div>
        <?php endif; ?>
    </div>
    
    <!-- OpCache Tab -->
    <div id="tab-opcache" class="dev-tab-content" style="display: none;">
        <h5>OpCache Status: 
            <span style="color: <?= $data['opcache']['enabled'] ? '#68d391' : '#fc8181' ?>">
                <?= $data['opcache']['enabled'] ? 'Enabled' : 'Disabled' ?>
            </span>
        </h5>
        
        <?php if ($data['opcache']['enabled']): ?>
            <div style="background: #2d3748; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Cached Scripts</div>
                        <div style="font-size: 16px; color: #68d391;"><?= $data['opcache']['cached_scripts'] ?></div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Hit Rate</div>
                        <div style="font-size: 16px; 
                            color: <?= $data['opcache']['hit_rate'] > 90 ? '#68d391' : 
                                   ($data['opcache']['hit_rate'] > 70 ? '#d69e2e' : '#fc8181') ?>">
                            <?= $data['opcache']['hit_rate'] ?>%
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Memory Used</div>
                        <div style="font-size: 16px; color: #68d391;">
                            <?= $data['opcache']['memory_used_mb'] ?> MB
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Memory Free</div>
                        <div style="font-size: 16px; color: #68d391;">
                            <?= $data['opcache']['memory_free_mb'] ?> MB
                        </div>
                    </div>
                </div>
                
                <!-- Memory Usage Bar -->
                <?php if ($data['opcache']['memory_total_mb'] > 0): 
                    $memoryPercent = min(100, ($data['opcache']['memory_used_mb'] / $data['opcache']['memory_total_mb']) * 100);
                ?>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Memory Usage</span>
                            <span><?= round($memoryPercent, 1) ?>%</span>
                        </div>
                        <div style="width: 100%; height: 8px; background: #4a5568; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $memoryPercent ?>%; 
                                 background: <?= $memoryPercent > 90 ? '#fc8181' : 
                                             ($memoryPercent > 70 ? '#d69e2e' : '#68d391') ?>;"></div>
                        </div>
                        <div style="font-size: 10px; color: #a0aec0; text-align: center; margin-top: 3px;">
                            <?= $data['opcache']['memory_used_mb'] ?>MB / <?= $data['opcache']['memory_total_mb'] ?>MB
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Hit/Miss Stats -->
            <div style="background: #2d3748; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                <h6 style="margin-top: 0;">Cache Statistics</h6>
                <div style="display: flex; justify-content: space-between;">
                    <div style="text-align: center;">
                        <div style="font-size: 12px; color: #a0aec0;">Hits</div>
                        <div style="font-size: 18px; color: #68d391;"><?= $data['opcache']['hits'] ?></div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 12px; color: #a0aec0;">Misses</div>
                        <div style="font-size: 18px; color: <?= $data['opcache']['misses'] > 0 ? '#d69e2e' : '#68d391' ?>">
                            <?= $data['opcache']['misses'] ?>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 12px; color: #a0aec0;">Total</div>
                        <div style="font-size: 18px; color: #68d391;"><?= $data['opcache']['hits'] + $data['opcache']['misses'] ?></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="background: #2d3748; padding: 20px; border-radius: 4px; text-align: center; color: #a0aec0;">
                OpCache is not enabled or not available
            </div>
        <?php endif; ?>
    </div>
    
    <!-- JIT Tab -->
    <div id="tab-jit" class="dev-tab-content" style="display: none;">
        <h5>JIT Status: 
            <span style="color: <?= $data['jit']['enabled'] ? '#68d391' : '#fc8181' ?>">
                <?= $data['jit']['enabled'] ? 'Enabled' : 'Disabled' ?>
            </span>
        </h5>
        
        <?php if ($data['jit']['enabled']): ?>
            <div style="background: #2d3748; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Mode</div>
                        <div style="font-size: 16px; color: #68d391; text-transform: capitalize;">
                            <?= $data['jit']['mode'] ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Buffer Size</div>
                        <div style="font-size: 16px; color: #68d391;">
                            <?= $data['jit']['buffer_size_mb'] ?? 'N/A' ?> MB
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Compiled Functions</div>
                        <div style="font-size: 16px; color: #68d391;">
                            <?= $data['jit']['compiled_functions'] ?? 0 ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Performance Impact</div>
                        <div style="font-size: 16px; color: 
                            <?= ($data['jit']['performance_impact'] ?? 'low') === 'high' ? '#68d391' : 
                               (($data['jit']['performance_impact'] ?? 'low') === 'moderate' ? '#d69e2e' : '#a0aec0') ?>">
                            <?= ucfirst($data['jit']['performance_impact'] ?? 'low') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Buffer Usage Bar -->
                <?php if (isset($data['jit']['buffer_usage_percent'])): ?>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Buffer Usage</span>
                            <span><?= $data['jit']['buffer_usage_percent'] ?>%</span>
                        </div>
                        <div style="width: 100%; height: 8px; background: #4a5568; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $data['jit']['buffer_usage_percent'] ?>%; 
                                 background: <?= $data['jit']['buffer_usage_percent'] > 90 ? '#fc8181' : 
                                             ($data['jit']['buffer_usage_percent'] > 70 ? '#d69e2e' : '#68d391') ?>;"></div>
                        </div>
                        <div style="font-size: 10px; color: #a0aec0; text-align: center; margin-top: 3px;">
                            <?= $data['jit']['buffer_used_mb'] ?? 0 ?>MB / <?= $data['jit']['buffer_size_mb'] ?? 0 ?>MB
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- JIT Configuration -->
            <?php if (isset($data['jit']['config'])): ?>
                <h6>Configuration</h6>
                <pre style="background: #2d3748; padding: 8px; border-radius: 4px; overflow: auto; max-height: 150px;">
<?= json_encode($data['jit']['config'], JSON_PRETTY_PRINT) ?>
                </pre>
            <?php endif; ?>
        <?php else: ?>
            <div style="background: #2d3748; padding: 20px; border-radius: 4px; text-align: center; color: #a0aec0;">
                JIT is not enabled. Requires PHP 8.0+ with OpCache enabled.
            </div>
        <?php endif; ?>
    </div>

    <!-- Добавляем новую вкладку Gzip Tab -->
    <div id="tab-gzip" class="dev-tab-content" style="display: none;">
        <h5>Gzip Compression: 
            <span style="color: <?= $data['gzip']['enabled'] ? '#68d391' : '#fc8181' ?>">
                <?= $data['gzip']['enabled'] ? 'Enabled' : 'Disabled' ?>
            </span>
        </h5>
            
        <?php if ($data['gzip']['enabled']): ?>
            <div style="background: #2d3748; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Compression Level</div>
                        <div style="font-size: 16px; 
                            color: <?= $data['gzip']['compression_level'] >= 6 ? '#68d391' : 
                                   ($data['gzip']['compression_level'] >= 4 ? '#d69e2e' : '#fc8181') ?>">
                            <?= $data['gzip']['compression_level'] ?>
                            <small style="color: #a0aec0; font-size: 10px;">
                                (<?= $data['gzip']['compression_level'] == 1 ? 'Fastest' : 
                                   ($data['gzip']['compression_level'] == 6 ? 'Optimal' : 
                                   ($data['gzip']['compression_level'] == 9 ? 'Maximum' : 'Balanced')) ?>)
                            </small>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Estimated Ratio</div>
                        <div style="font-size: 16px; 
                            color: <?= $data['gzip']['compression_ratio'] > 75 ? '#68d391' : 
                                   ($data['gzip']['compression_ratio'] > 60 ? '#d69e2e' : '#fc8181') ?>">
                            <?= $data['gzip']['compression_ratio'] ?>%
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Requests Compressed</div>
                        <div style="font-size: 16px; color: #68d391;">
                            <?= $data['gzip']['statistics']['requests_compressed'] ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: #a0aec0;">Bytes Saved</div>
                        <div style="font-size: 16px; color: #68d391;">
                            <?= PerformanceProfiler::formatBytes($data['gzip']['statistics']['bytes_saved']) ?>
                        </div>
                    </div>
                </div>

                <!-- Client Support -->
                <div style="margin-top: 10px;">
                    <div style="font-size: 10px; color: #a0aec0; margin-bottom: 5px;">Client Support</div>
                    <div style="padding: 5px; background: #4a5568; border-radius: 3px;">
                        <?php
                        $acceptEncoding = $data['request']['accept_encoding'] ?? '';
                        $supportsGzip = stripos($acceptEncoding, 'gzip') !== false;
                        $supportsDeflate = stripos($acceptEncoding, 'deflate') !== false;
                        ?>
                        <span style="color: <?= $supportsGzip ? '#68d391' : '#fc8181' ?>;">
                            Gzip: <?= $supportsGzip ? '✓' : '✗' ?>
                        </span>
                        <span style="margin-left: 10px; color: <?= $supportsDeflate ? '#68d391' : '#fc8181' ?>;">
                            Deflate: <?= $supportsDeflate ? '✓' : '✗' ?>
                        </span>
                        <?php if ($acceptEncoding): ?>
                            <div style="font-size: 9px; color: #a0aec0; margin-top: 3px;">
                                Accept-Encoding: <?= htmlspecialchars(substr($acceptEncoding, 0, 100)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
                        
            <!-- Compression Statistics -->
            <h6>Compression Statistics</h6>
            <div style="background: #2d3748; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <div style="text-align: center;">
                        <div style="font-size: 12px; color: #a0aec0;">Avg. Ratio</div>
                        <div style="font-size: 18px; color: 
                            <?= $data['gzip']['statistics']['average_ratio'] > 75 ? '#68d391' : 
                               ($data['gzip']['statistics']['average_ratio'] > 60 ? '#d69e2e' : '#fc8181') ?>">
                            <?= $data['gzip']['statistics']['average_ratio'] ?>%
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 12px; color: #a0aec0;">Bandwidth Saved</div>
                        <div style="font-size: 18px; color: #68d391;">
                            ~<?= round($data['gzip']['statistics']['average_ratio'] ?? 0) ?>%
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 12px; color: #a0aec0;">Zlib Version</div>
                        <div style="font-size: 18px; color: #68d391;">
                            <?= phpversion('zlib') ?: 'N/A' ?>
                        </div>
                    </div>
                </div>
                        
                <!-- Recent Requests -->
                <?php if (!empty($data['gzip']['statistics']['last_requests'])): ?>
                    <div style="margin-top: 10px;">
                        <div style="font-size: 10px; color: #a0aec0; margin-bottom: 5px;">Recent Compressed Requests</div>
                        <div style="max-height: 150px; overflow-y: auto;">
                            <?php foreach (array_reverse($data['gzip']['statistics']['last_requests']) as $request): ?>
                                <div style="background: #4a5568; padding: 6px; margin-bottom: 5px; border-radius: 3px; border-left: 3px solid #68d391;">
                                    <div style="display: flex; justify-content: space-between; font-size: 9px;">
                                        <span title="<?= htmlspecialchars($request['url']) ?>">
                                            <?= htmlspecialchars(self::truncate($request['url'], 30)) ?>
                                        </span>
                                        <span style="color: #68d391;">
                                            <?= $request['compression_ratio'] ?>%
                                        </span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-size: 8px; color: #a0aec0;">
                                        <span><?= $request['content_type'] ?></span>
                                        <span>
                                            <?= PerformanceProfiler::formatBytes($request['original_size'] ?? 0) ?> → 
                                            <?= PerformanceProfiler::formatBytes($request['compressed_size'] ?? 0) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
                            
            <!-- Configuration -->
            <h6>Configuration</h6>
            <div style="background: #2d3748; padding: 8px; border-radius: 4px; font-size: 10px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">
                    <div>zlib.output_compression:</div>
                    <div style="color: #68d391;"><?= ini_get('zlib.output_compression') ?></div>
                            
                    <div>zlib.output_compression_level:</div>
                    <div style="color: #68d391;"><?= ini_get('zlib.output_compression_level') ?></div>
                            
                    <div>zlib.output_handler:</div>
                    <div style="color: #68d391;"><?= ini_get('zlib.output_handler') ?: 'none' ?></div>
                </div>
            </div>
                            
            <!-- Gzip Actions -->
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button onclick="toggleGzip()" style="background: #805ad5; border: none; color: white; 
                       padding: 5px 10px; border-radius: 3px; cursor: pointer; flex: 1;">
                    <?= $data['gzip']['enabled'] ? 'Disable Gzip' : 'Enable Gzip' ?>
                </button>
                <button onclick="clearGzipStats()" style="background: #3182ce; border: none; color: white; 
                       padding: 5px 10px; border-radius: 3px; cursor: pointer; flex: 1;">
                    Clear Stats
                </button>
            </div>
        <?php else: ?>
            <div style="background: #2d3748; padding: 20px; border-radius: 4px; text-align: center; color: #a0aec0;">
                <p>Gzip output compression is disabled.</p>
                <p style="font-size: 10px; margin-top: 10px;">
                    Enable for significant bandwidth savings (typically 60-80% reduction).
                </p>
                <button onclick="toggleGzip()" style="background: #68d391; border: none; color: white; 
                       padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-top: 10px;">
                    Enable Gzip Compression
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Queries Tab (оставляем без изменений) -->
    <div id="tab-queries" class="dev-tab-content" style="display: none;">
        <h5>SQL Queries (<?= $data['performance']['query_count'] ?>)</h5>
        <?php if (!empty($data['queries'])): ?>
            <?php foreach ($data['queries'] as $i => $query): ?>
                <div style="background: #2d3748; padding: 8px; margin-bottom: 8px; border-radius: 4px; border-left: 3px solid #68d391;">
                    <div style="font-weight: bold; color: #68d391;">Query #<?= $i+1 ?> (<?= $query['duration'] ?>ms)</div>
                    <div style="font-family: monospace; font-size: 10px; margin: 5px 0; color: #cbd5e0;"><?= htmlspecialchars($query['sql']) ?></div>
                    <?php if ($query['error']): ?>
                        <div style="color: #fc8181;">Error: <?= htmlspecialchars($query['error']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="color: #a0aec0; text-align: center; padding: 20px;">No queries logged</div>
        <?php endif; ?>
    </div>
    
    <!-- Request Tab (оставляем без изменений) -->
    <div id="tab-request" class="dev-tab-content" style="display: none;">
        <h5>Request Details</h5>
        <div style="background: #2d3748; padding: 10px; border-radius: 4px;">
            <div><strong>URL:</strong> <?= htmlspecialchars($data['request']['url']) ?></div>
            <div><strong>Method:</strong> <?= $data['request']['method'] ?></div>
            <div><strong>IP:</strong> <?= $data['request']['ip'] ?></div>
            <div><strong>User Agent:</strong> <small><?= htmlspecialchars($data['request']['user_agent']) ?></small></div>
            
            <h6 style="margin-top: 10px;">GET Parameters:</h6>
            <pre style="background: #4a5568; padding: 5px; border-radius: 3px; font-size: 10px;">
<?= json_encode($data['request']['get_params'], JSON_PRETTY_PRINT) ?>
            </pre>
        </div>
    </div>
    
    <!-- System Tab (оставляем без изменений) -->
    <div id="tab-system" class="dev-tab-content" style="display: none;">
        <h5>System Information</h5>
        <pre style="background: #2d3748; padding: 8px; border-radius: 4px; overflow: auto; max-height: 200px;">
<?= json_encode($data['environment'], JSON_PRETTY_PRINT) ?>
        </pre>
        
        <h5 style="margin-top: 15px;">Memory Usage</h5>
        <div style="background: #2d3748; padding: 8px; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Used:</span>
                <span><?= round($data['performance']['memory_usage'] / 1024 / 1024, 2) ?> MB</span>
            </div>
            <div style="width: 100%; height: 8px; background: #4a5568; border-radius: 4px; overflow: hidden;">
                <div style="height: 100%; width: <?= min(100, ($data['performance']['memory_usage'] / $data['performance']['memory_limit']) * 100) ?>%; background: #68d391;"></div>
            </div>
        </div>
    </div>
    
    <!-- Assets Tab (оставляем без изменений) -->
    <div id="tab-assets" class="dev-tab-content" style="display: none;">
        <h5>Asset Watcher</h5>
        <div style="background: #2d3748; padding: 10px; border-radius: 4px;">
            <!-- <button onclick="checkForUpdates()" style="background: #805ad5; border: none; color: white; 
                   padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-bottom: 10px;">
                🔄 Check for Updates
            </button> -->
            <!-- <div id="asset-status" style="color: #a0aec0;">
                Checking for file changes...
            </div> -->
        </div>
    </div>
</div>

<script>
// Toolbar functionality
function toggleToolbar() {
    const toolbar = document.getElementById('dev-toolbar');
    toolbar.style.display = toolbar.style.display === 'none' ? 'flex' : 'none';
}

function toggleDevPanel() {
    const panel = document.getElementById('dev-panel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function closeDevPanel() {
    document.getElementById('dev-panel').style.display = 'none';
}
        
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.dev-tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.dev-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).style.display = 'block';
    
    // Add active class to clicked tab
    event.target.classList.add('active');
}
        
function clearDevCache() {
    if (confirm('Clear development cache?')) {
        fetch('/_dev/cache/clear', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Cache cleared successfully!');
                    location.reload();
                }
            });
    }
}

function resetOpCache() {
    if (confirm('Reset OpCache and JIT? This will clear all compiled code.')) {
        fetch('/_dev/cache/reset', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('OpCache and JIT reset successfully!');
                    location.reload();
                }
            });
    }
}
    
// function checkForUpdates() {
    // const statusEl = document.getElementById('asset-status');
    // statusEl.innerHTML = 'Checking for changes...';
    // statusEl.style.color = '#a0aec0';
    
    // fetch('/_dev/assets/check')
    //     .then(r => r.json())
    //     .then(data => {
    //         if (data.changed && data.files.length > 0) {
    //             statusEl.innerHTML = `Found ${data.files.length} changed file(s). Reload page?`;
    //             statusEl.style.color = '#68d391';
                
    //             if (confirm(`Found ${data.files.length} changed file(s). Reload page?`)) {
    //                 location.reload();
    //             }
    //         } else {
    //             statusEl.innerHTML = 'No changes detected';
    //             statusEl.style.color = '#a0aec0';
    //         }
    //     })
    //     .catch(err => {
    //         statusEl.innerHTML = 'Error checking for updates';
    //         statusEl.style.color = '#fc8181';
    //     });
// }

// TEST
function toggleGzip() {
    fetch('/_dev/gzip/toggle', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Gzip ' + (data.enabled ? 'enabled' : 'disabled') + '. Page will reload.');
                location.reload();
            }
        });
}

function clearGzipStats() {
    if (confirm('Clear Gzip statistics?')) {
        fetch('/_dev/gzip/clear-stats', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Gzip statistics cleared!');
                    showTab('gzip'); // Обновляем вкладку
                    // Перезагружаем данные панели
                    setTimeout(() => location.reload(), 500);
                }
            });
    }
}
    
// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'd') {
        toggleToolbar();
    }
    if (e.ctrlKey && e.key === 'p') {
        toggleDevPanel();
    }
    if (e.ctrlKey && e.key === 'r') {
        resetOpCache();
    }
});
    
// Auto-check for updates every 30 seconds
// setInterval(checkForUpdates, 30000);

// Auto-switch to OpCache tab if there are recommendations
<?php if (!empty($data['opcache']['recommendations'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        showTab('opcache');
    }, 500);
});
<?php endif; ?>
</script>
    
<style>
.dev-tab {
    background: #4a5568;
    border: none;
    color: #cbd5e0;
    padding: 5px 10px;
    border-radius: 3px 3px 0 0;
    font-size: 10px;
    cursor: pointer;
    transition: background 0.2s;
    margin-bottom: -1px;
}
    
.dev-tab:hover {
    background: #5a6578;
}
    
.dev-tab.active {
    background: #2d3748;
    color: #81e6d9;
    border-bottom: 2px solid #81e6d9;
}

.dev-tab-content {
    animation: fadeIn 0.3s;
}
    
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Scrollbar styling */
#dev-panel::-webkit-scrollbar {
    width: 6px;
}

#dev-panel::-webkit-scrollbar-track {
    background: #2d3748;
}

#dev-panel::-webkit-scrollbar-thumb {
    background: #4a5568;
    border-radius: 3px;
}

#dev-panel::-webkit-scrollbar-thumb:hover {
    background: #5a6578;
}
</style>