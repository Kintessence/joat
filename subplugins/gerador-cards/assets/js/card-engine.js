document.addEventListener("DOMContentLoaded", function() {
    
    class CardEngine {
        constructor() {
            // Lê dinamicamente do PHP via CG_ENV ou usa fallback seguro
            const config = (typeof CG_ENV !== 'undefined' && CG_ENV.f2_config) ? CG_ENV.f2_config : {};

            this.MAX_CARACTERES = config.max_chars || 32;
            this.LARGURA_UTIL_FIXA = config.util_width || 325;
            this.TAMANHO_MAXIMO = config.max_font || 38;
            this.TAMANHO_MINIMO = config.min_font || 30;

            this.canvas = document.createElement('canvas');
            this.context = this.canvas.getContext('2d');

            this.initDOM();
            this.initEvents();
            this.ajustarCampo2();
            this.atualizarEstadosBotoes();
        }

        initDOM() {
            this.f2Title = document.getElementById('f2');
            this.bgRender = document.getElementById('card-bg-render');
            this.maskRender = document.getElementById('card-mask-render');
            this.cardContainer = document.getElementById('card-to-render');

            this.rangeY = document.getElementById('cg-range-y');
            this.valY = document.getElementById('cg-val-y');
            this.rangeZoom = document.getElementById('cg-range-zoom');
            this.valZoom = document.getElementById('cg-val-zoom');

            this.btnSave = document.getElementById('cg-btn-save');
            this.btnGenerate = document.getElementById('cg-btn-generate');
            this.statusMessage = document.getElementById('cg-status-message');

            this.dateInput = document.getElementById('cg-date-input');
            this.timeInput = document.getElementById('time-input');
            this.dateDisplay = document.getElementById('f5_1_date_display');
            this.timeDisplay = document.getElementById('f5_1_time_display');
            this.maskSelector = document.getElementById('cg-mask-selector');
        }

        initEvents() {
            const self = this;

            if (this.f2Title) {
                this.f2Title.addEventListener('input', () => {
                    window.requestAnimationFrame(() => self.ajustarCampo2());
                    self.notificarAlteracao();
                });

                this.f2Title.addEventListener('keydown', (e) => {
                    if (e.key === "Enter") { e.preventDefault(); return false; }
                });

                this.f2Title.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const text = (e.originalEvent || e).clipboardData.getData('text/plain');
                    document.execCommand('insertText', false, text);
                    self.ajustarCampo2();
                    self.notificarAlteracao();
                });
            }

            if (this.maskSelector) {
                this.maskSelector.addEventListener('change', function() {
                    self.maskRender.style.backgroundImage = this.value ? `url(${this.value})` : 'none';
                    self.notificarAlteracao();
                });
            }

            if (this.rangeY) {
                this.rangeY.addEventListener('input', function() {
                    self.valY.innerText = this.value;
                    self.bgRender.style.backgroundPositionY = `${this.value}px`;
                    self.notificarAlteracao();
                });
            }

            if (this.rangeZoom) {
                this.rangeZoom.addEventListener('input', function() {
                    self.valZoom.innerText = this.value;
                    self.bgRender.style.setProperty('background-size', `${this.value}% auto`, 'important');
                    self.notificarAlteracao();
                });
            }

            const uploaderType = document.getElementById('cg-uploader-type')?.value;
            if (uploaderType === 'wp_media') {
                let customUploader;
                const mediaBtn = document.getElementById('cg-media-uploader');
                if (mediaBtn) {
                    mediaBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (customUploader) { customUploader.open(); return; }

                        customUploader = wp.media({
                            title: 'Selecione a Foto de Fundo',
                            button: { text: 'Aplicar no Card' },
                            multiple: false
                        });

                        customUploader.on('select', () => {
                            const attachment = customUploader.state().get('selection').first().toJSON();
                            self.bgRender.style.backgroundImage = `url(${attachment.url})`;
                            self.bgRender.style.setProperty('background-size', `${self.rangeZoom.value}% auto`, 'important');
                            document.getElementById('cg-position-y-wrapper').style.display = 'block';
                            document.getElementById('cg-zoom-wrapper').style.display = 'block';
                            self.atualizarEstadosBotoes();
                            self.notificarAlteracao();
                        });
                        customUploader.open();
                    });
                }
            } else {
                const localBtn = document.getElementById('cg-local-uploader');
                if (localBtn) {
                    localBtn.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                self.bgRender.style.backgroundImage = `url(${event.target.result})`;
                                self.bgRender.style.setProperty('background-size', `${self.rangeZoom.value}% auto`, 'important');
                                document.getElementById('cg-position-y-wrapper').style.display = 'block';
                                document.getElementById('cg-zoom-wrapper').style.display = 'block';
                                self.atualizarEstadosBotoes();
                                self.notificarAlteracao();
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
            }

            if (this.dateInput) this.dateInput.addEventListener('input', () => self.atualizarDataHora());
            if (this.timeInput) this.timeInput.addEventListener('input', () => self.atualizarDataHora());

            if (this.btnSave) this.btnSave.addEventListener('click', () => self.salvarCard());
            if (this.btnGenerate) this.btnGenerate.addEventListener('click', () => self.baixarImagemComSalvamentoPrevio());
        }

        notificarAlteracao() {
            if (this.btnSave && this.btnSave.disabled) {
                this.btnSave.disabled = false;
                this.btnSave.classList.remove('disabled-gray');
            }
        }

        atualizarEstadosBotoes() {
            const temImagem = this.bgRender.style.backgroundImage && 
                              this.bgRender.style.backgroundImage !== 'none' && 
                              this.bgRender.style.backgroundImage !== 'url("")';

            if (this.btnGenerate) {
                this.btnGenerate.disabled = !temImagem;
                this.btnGenerate.classList.toggle('disabled-gray', !temImagem);
            }
        }

        ajustarCampo2() {
            if (!this.f2Title) return;

            this.f2Title.scrollTop = 0;
            let texto = this.f2Title.innerText || this.f2Title.textContent;
            let textoLimpo = texto.replace(/\n/g, " ");

            if (textoLimpo.length > this.MAX_CARACTERES) {
                textoLimpo = textoLimpo.substring(0, this.MAX_CARACTERES);
                this.f2Title.innerText = textoLimpo;

                const range = document.createRange();
                const sel = window.getSelection();
                range.selectNodeContents(this.f2Title);
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
            }

            if (!textoLimpo.trim()) {
                this.f2Title.style.fontSize = this.TAMANHO_MAXIMO + 'px';
                this.f2Title.style.whiteSpace = 'nowrap';
                return;
            }

            const fontFamily = getComputedStyle(this.f2Title).fontFamily;
            let fontSizeAlvo = this.TAMANHO_MAXIMO;
            this.context.font = `850 ${fontSizeAlvo}px ${fontFamily}`;
            let larguraTexto = this.context.measureText(textoLimpo.toUpperCase()).width;

            while (larguraTexto > this.LARGURA_UTIL_FIXA && fontSizeAlvo > this.TAMANHO_MINIMO) {
                fontSizeAlvo--;
                this.context.font = `850 ${fontSizeAlvo}px ${fontFamily}`;
                larguraTexto = this.context.measureText(textoLimpo.toUpperCase()).width;
            }

            this.context.font = `850 ${this.TAMANHO_MINIMO}px ${fontFamily}`;
            let larguraNoMinimo = this.context.measureText(textoLimpo.toUpperCase()).width;

            if (larguraNoMinimo > this.LARGURA_UTIL_FIXA) {
                this.f2Title.style.fontSize = this.TAMANHO_MINIMO + 'px';
                this.f2Title.style.whiteSpace = 'pre-wrap';
            } else {
                this.f2Title.style.fontSize = fontSizeAlvo + 'px';
                this.f2Title.style.whiteSpace = 'nowrap';
            }
        }

        atualizarDataHora() {
            const diasSemana = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
            if (this.dateInput && this.dateInput.value) {
                const parts = this.dateInput.value.split('-');
                const obj = new Date(parts[0], parts[1] - 1, parts[2]);
                const dia = String(obj.getDate()).padStart(2, '0');
                const mes = String(obj.getMonth() + 1).padStart(2, '0');
                const extenso = diasSemana[obj.getDay()];
                this.dateDisplay.innerHTML = `${dia}/${mes} <span class="cg-dia-semana">${extenso}</span>`;
            }
            if (this.timeInput && this.timeInput.value) {
                const tParts = this.timeInput.value.split(':');
                const horas = parseInt(tParts[0], 10);
                const minutos = parseInt(tParts[1], 10);
                this.timeDisplay.innerText = minutos === 0 ? `${horas}h` : `${horas}h${String(minutos).padStart(2, '0')}`;
            }
        }

        async salvarCard() {
            this.statusMessage.style.color = '#007cba';
            this.statusMessage.innerText = 'Salvando alterações...';

            const data = new URLSearchParams({
                action: 'cg_save_card_data',
                card_id: document.getElementById('card-post-id').value,
                title: document.getElementById('card-internal-title').value,
                f1: document.getElementById('f1').innerText,
                f2: this.f2Title.innerText,
                f3: document.getElementById('f3').innerText,
                f4: document.getElementById('f4').innerHTML,
                date_raw: this.dateInput.value,
                time_raw: this.timeInput.value,
                f5_1_compiled: this.dateDisplay.innerHTML,
                f5_1_time_compiled: this.timeDisplay.innerText,
                f5_2: document.getElementById('f5_2').innerText,
                f5_3: document.getElementById('f5_3').innerText,
                bg_img: this.bgRender.style.backgroundImage.replace(/^url\(["']?/, '').replace(/["']?\)$/, ''),
                mask_img: document.getElementById('cg-mask-selector').value,
                bg_position_y: this.rangeY.value,
                bg_zoom: this.rangeZoom.value
            });

            try {
                const response = await fetch(CG_ENV.ajax_url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: data
                });
                const res = await response.json();

                if (res.success) {
                    this.statusMessage.style.color = '#46b450';
                    this.statusMessage.innerText = '✓ Salvo com sucesso!';
                    document.getElementById('card-post-id').value = res.data.card_id;
                    return true;
                } else {
                    this.statusMessage.style.color = '#cc0000';
                    this.statusMessage.innerText = 'Erro ao salvar os dados.';
                    return false;
                }
            } catch (e) {
                this.statusMessage.style.color = '#cc0000';
                this.statusMessage.innerText = 'Erro na requisição AJAX.';
                return false;
            }
        }

        async baixarImagemComSalvamentoPrevio() {
            const salvo = await this.salvarCard();
            if (!salvo) return;

            this.statusMessage.innerText = 'Gerando PNG de alta fidelidade...';

            html2canvas(this.cardContainer, {
                scale: 2.666666,
                useCORS: true,
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = document.getElementById('card-internal-title').value + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                this.statusMessage.innerText = '';
            });
        }
    }

    new CardEngine();
});