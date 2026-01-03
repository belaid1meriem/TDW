<?php



namespace Core;

abstract class Controller {
    protected $request;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }
    
    protected function render(View $view) {
        $view->render();
    }
    
    protected function redirect($url, $code = 302) {
        http_response_code($code);
        header("Location: $url");
        exit;
    }
    
    protected function back() {
        $referer = $this->request->header('Referer', '/');
        $this->redirect($referer);
    }
    
    protected function redirectWithSuccess($url, $message) {
        Session::flash('success', $message);
        $this->redirect($url);
    }
    
    protected function redirectWithError($url, $message) {
        Session::flash('error', $message);
        $this->redirect($url);
    }
    
    
    protected function uploadFile($fileKey, $destination = 'uploads/', $allowedTypes = []) {
        if (!$this->request->hasFile($fileKey)) {
            return null;
        }
        
        $file = $this->request->file($fileKey);
        
        if (!empty($allowedTypes)) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedTypes)) {
                throw new \Exception("Invalid file type. Allowed: " . implode(', ', $allowedTypes));
            }
        }
        
        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = $destination . $filename;
        
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath;
        }
        
        throw new \Exception("Failed to upload file");
    }
    
    protected function download($filePath, $filename = null) {
        if (!file_exists($filePath)) {
            http_response_code(404);
            // $view = new \App\Views\ErrorView('File not found');
            // $this->render($view);
            exit;
        }
        
        if ($filename === null) {
            $filename = basename($filePath);
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($filePath);
        exit;
    }
}