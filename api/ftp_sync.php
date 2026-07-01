<?php
/**
 * WebEstoque FTP Deployer
 * Sincronização recursiva de arquivos via FTP
 */

class FtpDeployer {
    private $conn;
    private $host;
    private $user;
    private $pass;
    private $port;
    private $remote_path;
    private $error_message = '';
    private $ignore_patterns = [
        '.git',
        '.gitignore',
        'node_modules',
        'tmp',
        '.vscode',
        'vercel.json',
        '.gemini',
        'api/db.php',
        'api/config.php'
    ];

    public function getErrorMessage() {
        return $this->error_message;
    }

    public function setIgnorePatterns($patterns) {
        $this->ignore_patterns = $patterns;
    }

    public function addIgnorePattern($pattern) {
        $this->ignore_patterns[] = $pattern;
    }

    public function __construct($config) {
        $this->host = $config['ftp_host'];
        $this->user = $config['ftp_user'];
        $this->pass = $config['ftp_pass'];
        $this->port = $config['ftp_port'] ?? 21;
        $this->remote_path = rtrim($config['ftp_path'], '/');
    }

    public function connect() {
        $this->conn = @ftp_connect($this->host, $this->port, 10);
        if (!$this->conn) {
            $this->error_message = 'Não foi possível conectar ao host ' . $this->host . ' na porta ' . $this->port . '. Verifique a conexão de rede.';
            return false;
        }
        
        if (!@ftp_login($this->conn, $this->user, $this->pass)) {
            $this->error_message = 'Falha na autenticação do FTP. Usuário ou senha incorretos.';
            ftp_close($this->conn);
            return false;
        }
        
        ftp_pasv($this->conn, true); // Modo Passivo
        return true;
    }

    public function disconnect() {
        if ($this->conn) ftp_close($this->conn);
    }

    /**
     * Sincroniza uma pasta recursivamente
     */
    public function deploy($local_path, $callback = null) {
        return $this->recursiveUpload($local_path, $this->remote_path, $callback);
    }

    private function recursiveUpload($local_dir, $remote_dir, $callback) {
        $handle = opendir($local_dir);
        if (!$handle) return false;

        // Tentar criar diretório remoto se não existir
        @ftp_mkdir($this->conn, $remote_dir);

        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') continue;
            
            $local_file = $local_dir . '/' . $file;
            $remote_file = $remote_dir . '/' . $file;

            // Pegar o caminho relativo para comparar corretamente com 'api/config.php'
            $root_path = str_replace('\\', '/', realpath(__DIR__ . '/../'));
            $rel_path = str_replace($root_path . '/', '', str_replace('\\', '/', realpath($local_file)));

            // Ignorar padrões
            if (in_array($file, $this->ignore_patterns) || in_array($rel_path, $this->ignore_patterns)) continue;

            if (is_dir($local_file)) {
                $this->recursiveUpload($local_file, $remote_file, $callback);
            } else {
                if ($callback) $callback($file, 'uploading');
                
                $upload = ftp_put($this->conn, $remote_file, $local_file, FTP_BINARY);
                
                if ($callback) $callback($file, $upload ? 'success' : 'error');
            }
        }
        closedir($handle);
        return true;
    }
}
