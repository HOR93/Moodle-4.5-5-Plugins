<?php

/**
 * Block projeto_transversal
 *
 * @package     block_projeto_transversal
 * @copyright   2025 Henrique <@aluno.unb.br>
 */
class block_projeto_transversal extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_projeto_transversal');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
    
        global $COURSE, $CFG, $DB;
        require_once($CFG->dirroot . '/course/lib.php');
    
        $format = course_get_format($COURSE);
        $numsections = $format->get_last_section_number();
    
        // Contagem de atividades
        $modinfo = get_fast_modinfo($COURSE);
        $assigncount = 0;
        $quizcount = 0;
        $resourcecount = 0;
    
        foreach ($modinfo->get_cms() as $cm) {
            if (!$cm->uservisible) continue;
    
            switch ($cm->modname) {
                case 'assign': $assigncount++; break;
                case 'quiz': $quizcount++; break;
                case 'resource':
                case 'page': $resourcecount++; break;
            }
        }
    
        $sectioninfo = "<p style='margin-top:15px; font-weight:bold;'>📘 Este curso possui <strong>$numsections</strong> tópicos.</p>";
        $atividadeinfo = "
            <div style='text-align:left; margin-top:10px; font-size:0.95em;'>
                <strong>Informações do Curso:</strong><br>
                Tarefas: <strong>$assigncount</strong><br>
                Questionários: <strong>$quizcount</strong><br>
                Arquivos: <strong>$resourcecount</strong><br>
            </div>";
    
        $html = <<<HTML
        <div id="pomodoro-container" style="font-family: sans-serif; text-align:center; padding: 20px; position:relative; border-radius:10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    
            <!-- alternar modo -->
            <button onclick="toggleDarkMode()" style="position:absolute; top:10px; right:10px; background:none; border:none; cursor:pointer; font-size:1.2em;">🌙</button>
    
            <div style="position:absolute; top:10px; left:10px; display:flex; gap:8px;">
                <a href="https://www.office.com/" target="_blank" title="Microsoft 365"><img src="https://static2.sharepointonline.com/files/fabric/assets/brand-icons/product/svg/office_48x1.svg" style="width:18px;"></a>
                <a href="https://drive.google.com" target="_blank" title="Google Drive"><img src="https://ssl.gstatic.com/images/branding/product/2x/drive_2020q4_48dp.png" style="width:18px;"></a>
                <a href="https://www.google.com" target="_blank" title="Google"><img src="https://www.google.com/favicon.ico" style="width:18px;"></a>
                <a href="https://chat.openai.com" target="_blank" title="ChatGPT"><img src="https://chat.openai.com/favicon.ico" style="width:18px;"></a>
                <a href="https://open.spotify.com" target="_blank" title="Spotify"><img src="https://open.spotify.com/favicon.ico" style="width:18px;"></a>
                <a href="https://www.symbolab.com/" target="_blank" title="Symbolab"><img src="https://www.symbolab.com/favicon.ico" style="width:18px;"></a>
                <a href="https://translate.google.com" target="_blank" title="Google Tradutor"><img src="https://translate.google.com/favicon.ico" style="width:18px;"></a>
                <a href="https://github.com" target="_blank" title="GitHub"><img src="https://github.com/favicon.ico" style="width:18px;"></a>
            </div>
    
            <h4 style="margin-top:30px;">Pomodoro</h4>
            <label>Tempo de concentração:</label><br>
            <input type="number" id="studytime" value="25" min="1" style="width:60px;"><br><br>
    
            <div style="display:flex; justify-content:center; gap:10px;">
                <button onclick="startPomodoro()" id="startBtn" class="round-button">Iniciar</button>
                <button onclick="pausePomodoro()" id="pauseBtn" class="round-button" style="display:none;">Pausar</button>
                <button onclick="resetPomodoro()" class="round-button">Resetar</button>
            </div>
    
            <div id="pomodoro-timer" style="font-size:2.5em; color:#ff4d4d; margin:15px 0;">--:--</div>
            <p id="pomodoro-status" style="font-weight:bold;"></p>
    
            <hr>
    

            <h5>Notas de Estudo</h5>
            <textarea id="notes" placeholder="..." style="width:100%; height:100px;"></textarea><br>
            <button onclick="exportNotesPDF()" class="round-button" style="margin-top:10px;">PDF</button>
    
            <hr>
    
            <h5>Youtube</h5>
            <input type="text" id="youtubeQuery" placeholder="..." style="width:70%; padding:6px;" />
            <button onclick="buscarYoutube()" class="round-button" style="margin-top:8px;">Buscar</button>
    
            $sectioninfo
            $atividadeinfo
        </div>
    
        <style>
            .round-button {
                padding: 10px 20px;
                border-radius: 999px;
                background-color: #007aff;
                color: white;
                border: none;
                font-weight: bold;
                cursor: pointer;
                transition: 0.3s ease;
            }
            .round-button:hover {
                background-color: #005ecb;
                transform: scale(1.05);
            }
            .dark-mode {
                background-color: #1e1e1e;
                color: #f0f0f0;
            }
            .dark-mode textarea, .dark-mode input {
                background-color: #2c2c2c;
                color: white;
                border-color: #555;
            }
            .dark-mode .round-button {
                filter: brightness(1.2);
            }
        </style>
    
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script>
            let timer;
            let isRunning = false;
            let isPaused = false;
            let remainingSeconds = 0;
    
            function startPomodoro() {
                if (isRunning && !isPaused) return;
                let minutes = parseInt(document.getElementById('studytime').value);
                if (isNaN(minutes) || minutes <= 0) minutes = 25;
                remainingSeconds = minutes * 60;
                isRunning = true;
                isPaused = false;
    
                document.getElementById('startBtn').style.display = 'none';
                document.getElementById('pauseBtn').style.display = 'inline-block';
                document.getElementById('pomodoro-status').innerText = "Foco!";
    
                timer = setInterval(updateTimer, 1000);
            }
    
            function pausePomodoro() {
                clearInterval(timer);
                isRunning = false;
                isPaused = true;
                document.getElementById('pauseBtn').style.display = 'none';
                document.getElementById('startBtn').style.display = 'inline-block';
                document.getElementById('pomodoro-status').innerText = "⏸️ Pausado";
            }
    
            function resetPomodoro() {
                clearInterval(timer);
                isRunning = false;
                isPaused = false;
                document.getElementById('pomodoro-timer').innerText = "--:--";
                document.getElementById('pauseBtn').style.display = 'none';
                document.getElementById('startBtn').style.display = 'inline-block';
                document.getElementById('pomodoro-status').innerText = "";
            }
    
            function updateTimer() {
                const min = Math.floor(remainingSeconds / 60);
                const sec = remainingSeconds % 60;
                document.getElementById('pomodoro-timer').innerText =
                    String(min).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
                remainingSeconds--;
                if (remainingSeconds < 0) {
                    clearInterval(timer);
                    isRunning = false;
                    document.getElementById('pomodoro-status').innerText = "✅ Concluiu! Parabens!";
                    document.getElementById('pauseBtn').style.display = 'none';
                    document.getElementById('startBtn').style.display = 'inline-block';
                    const audio = new Audio('https://actions.google.com/sounds/v1/alarms/digital_watch_alarm_long.ogg');
                    audio.play();
                }
            }
    
            function exportNotesPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                const content = document.getElementById('notes').value;
                doc.setFont("helvetica");
                doc.setFontSize(12);
                doc.text("Anotações Pomodoro", 10, 10);
                doc.text(content, 10, 20);
                doc.save("anotacoes-pomodoro.pdf");
            }
    
            function buscarYoutube() {
                const query = document.getElementById('youtubeQuery').value.trim();
                if (query) {
                    window.open("https://www.youtube.com/results?search_query=" + encodeURIComponent(query), '_blank');
                }
            }
    
            function toggleDarkMode() {
                const container = document.getElementById('pomodoro-container');
                container.classList.toggle('dark-mode');
                const btn = container.querySelector('button');
                btn.textContent = container.classList.contains('dark-mode') ? '☀️' : '🌙';
                localStorage.setItem('pomodoroDarkMode', container.classList.contains('dark-mode'));
            }
    
            document.addEventListener("DOMContentLoaded", () => {
                const container = document.getElementById('pomodoro-container');
                if (localStorage.getItem('pomodoroDarkMode') === 'true') {
                    container.classList.add('dark-mode');
                    container.querySelector('button').textContent = '☀️';
                }
    
                const notes = document.getElementById('notes');
                notes.value = localStorage.getItem('pomonotes') || '';
                notes.addEventListener('input', () => {
                    localStorage.setItem('pomonotes', notes.value);
                });
            });
        </script>
        HTML;
    
        $this->content = new stdClass();
        $this->content->text = $html;
        $this->content->footer = '';
        return $this->content;
    }
    
    
    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_projeto_transversal');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return [
            'course-view' => True,
        ];
    }
}
