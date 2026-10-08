(function(wp){
	if(!wp || !wp.blocks || !wp.element){
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useEffect = wp.element.useEffect;
	var useState = wp.element.useState;
	var registerBlockType = wp.blocks.registerBlockType;
	var createBlock = wp.blocks.createBlock;
	var cloneBlock = wp.blocks.cloneBlock;
	var InspectorControls = (wp.blockEditor && wp.blockEditor.InspectorControls) || (wp.editor && wp.editor.InspectorControls);
	var RichText = (wp.blockEditor && wp.blockEditor.RichText) || (wp.editor && wp.editor.RichText);
	var components = wp.components || {};
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var SelectControl = components.SelectControl;
	var Button = components.Button;
	var Notice = components.Notice;
	var Modal = components.Modal;
	var registerPlugin = wp.plugins && wp.plugins.registerPlugin;
	var useSelect = wp.data && wp.data.useSelect;
	var useDispatch = wp.data && wp.data.useDispatch;
	var useEntityProp = wp.coreData && wp.coreData.useEntityProp;
	var __ = (wp.i18n && wp.i18n.__) ? wp.i18n.__ : function(s){ return s; };

	var config = window.formlayer_form_editor || {};
	var blockDefs = config.blocks || [];

	function slugify(text){
		return String(text || 'field').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || 'field';
	}

	function optionLabel(opt){
		if(opt && typeof opt === 'object'){
			return opt.label || opt.value || '';
		}
		return String(opt || '');
	}

	function normalizeOptions(options){
		if(!options || !options.length){
			return [
				{ label: 'Option 1', value: 'Option 1', default: false },
				{ label: 'Option 2', value: 'Option 2', default: false }
			];
		}
		return options.map(function(opt){
			if(opt && typeof opt === 'object'){
				return {
					label: opt.label || opt.value || '',
					value: opt.value || opt.label || '',
					default: !!opt.default
				};
			}
			return { label: String(opt), value: String(opt), default: false };
		});
	}

	function previewInput(type, attributes){
		var inputStyle = {};
		if(attributes.styleBorderRadius){
			inputStyle.borderRadius = parseInt(attributes.styleBorderRadius, 10) + 'px';
		}
		return el('input', {
			type: type || 'text',
			className: 'formlayer-input',
			placeholder: attributes.placeholder || '',
			value: attributes.defaultValue || '',
			disabled: true,
			readOnly: true,
			style: inputStyle
		});
	}

	function previewFieldControl(def, attributes){
		var preview = def.preview || 'text';
		var options = normalizeOptions(attributes.options);

		switch(preview){
			case 'textarea':
				return el('textarea', {
					className: 'formlayer-input',
					placeholder: attributes.placeholder || '',
					value: attributes.defaultValue || '',
					rows: attributes.rows || 4,
					disabled: true,
					readOnly: true
				});
			case 'select':
				return el('div', { className: 'formlayer-select-wrap' },
					el('select', { className: 'formlayer-input', disabled: true },
						el('option', {}, attributes.placeholder || __('Select Option', 'formlayer')),
						options.map(function(opt, i){
							return el('option', { key: i }, optionLabel(opt));
						})
					)
				);
			case 'radio':
			case 'checkbox':
				return el('div', { className: 'formlayer-options-list' },
					options.map(function(opt, i){
						return el('label', { key: i, className: 'formlayer-option-label' },
							el('input', { type: preview, disabled: true, defaultChecked: !!opt.default }),
							el('span', {}, optionLabel(opt))
						);
					})
				);
			case 'name':
				var parts = [];
				if(attributes.enableFirstName !== false){
					parts.push(el('div', { key: 'first', className: 'formlayer-sub-field' },
						el('input', { type: 'text', className: 'formlayer-input', placeholder: attributes.placeholderFirst || __('First Name', 'formlayer'), disabled: true }),
						el('span', { className: 'formlayer-sub-label' }, attributes.labelFirst || __('First Name', 'formlayer'))
					));
				}
				if(attributes.enableMiddleName){
					parts.push(el('div', { key: 'middle', className: 'formlayer-sub-field' },
						el('input', { type: 'text', className: 'formlayer-input', placeholder: attributes.placeholderMiddle || __('Middle Name', 'formlayer'), disabled: true }),
						el('span', { className: 'formlayer-sub-label' }, attributes.labelMiddle || __('Middle Name', 'formlayer'))
					));
				}
				if(attributes.enableLastName !== false){
					parts.push(el('div', { key: 'last', className: 'formlayer-sub-field' },
						el('input', { type: 'text', className: 'formlayer-input', placeholder: attributes.placeholderLast || __('Last Name', 'formlayer'), disabled: true }),
						el('span', { className: 'formlayer-sub-label' }, attributes.labelLast || __('Last Name', 'formlayer'))
					));
				}
				return el('div', { className: 'formlayer-name-fields-wrap' }, parts);
			case 'rating':
				return el('div', { className: 'formlayer-rating' },
					[1,2,3,4,5].map(function(i){
						return el('span', { key: i, className: 'dashicons dashicons-star-filled' });
					})
				);
			case 'submit':
				var btnStyle = {};
				if(attributes.btnBgColor){ btnStyle.backgroundColor = attributes.btnBgColor; }
				if(attributes.btnTextColor){ btnStyle.color = attributes.btnTextColor; }
				if(attributes.styleBorderRadius){ btnStyle.borderRadius = parseInt(attributes.styleBorderRadius, 10) + 'px'; }
				return el('button', {
					type: 'button',
					className: 'formlayer-submit-btn btn-' + (attributes.btnSize || 'md'),
					style: btnStyle
				}, attributes.label || __('Submit Form', 'formlayer'));
			case 'section':
				return el('div', { className: 'formlayer-section-break' },
					el('h3', {}, attributes.label || __('Section', 'formlayer')),
					attributes.helpText ? el('p', {}, attributes.helpText) : null
				);
			case 'terms':
				return el('div', { className: 'formlayer-terms-wrap' },
					el('input', { type: 'checkbox', disabled: true }),
					el('span', { className: 'formlayer-terms-label' }, attributes.termsLabel || attributes.label || __('I agree to the Terms & Conditions', 'formlayer'))
				);
			case 'gdpr':
				return el('div', { className: 'formlayer-gdpr-wrap' },
					el('input', { type: 'checkbox', disabled: true }),
					el('div', { className: 'formlayer-gdpr-info' },
						el('div', { className: 'formlayer-gdpr-label' }, attributes.gdprLabel || attributes.label || __('Accept GDPR Policy', 'formlayer')),
						attributes.gdprDescription ? el('div', { className: 'formlayer-gdpr-desc' }, attributes.gdprDescription) : null
					)
				);
			case 'captcha':
				return el('div', { className: 'formlayer-editor-captcha-placeholder' },
					el('span', { className: 'dashicons dashicons-shield-alt' }),
					el('span', {}, (attributes.captchaProvider || 'hCaptcha') + ' ' + __('widget will appear on the frontend', 'formlayer'))
				);
			case 'file':
			case 'image':
			case 'camera':
				return el('div', { className: 'formlayer-file-upload-box' },
					el('span', {
						className: 'formlayer-file-fake-btn',
						style: {
							background: attributes.fileBtnBg || '#5525d6',
							color: attributes.fileBtnColor || '#fff'
						}
					}, __('Choose File', 'formlayer')),
					el('span', { className: 'formlayer-file-chosen-name' }, __('No file chosen', 'formlayer'))
				);
			case 'address':
				return el('div', { className: 'formlayer-grid formlayer-address-grid' },
					attributes.enableStreet !== false ? el('div', { className: 'formlayer-sub-field full-width' },
						el('input', { type: 'text', className: 'formlayer-input', placeholder: __('Street Address', 'formlayer'), disabled: true })
					) : null,
					attributes.enableCity !== false ? el('div', { className: 'formlayer-sub-field' },
						el('input', { type: 'text', className: 'formlayer-input', placeholder: __('City', 'formlayer'), disabled: true })
					) : null,
					attributes.enableState !== false ? el('div', { className: 'formlayer-sub-field' },
						el('input', { type: 'text', className: 'formlayer-input', placeholder: __('State / Province', 'formlayer'), disabled: true })
					) : null
				);
			case 'hidden':
				return el('div', { className: 'formlayer-editor-hidden-note' },
					el('span', { className: 'dashicons dashicons-hidden' }),
					el('span', {}, __('Hidden field', 'formlayer') + ': ' + (attributes.nameAttr || attributes.label || 'field'))
				);
			case 'email':
				return previewInput('email', attributes);
			case 'number':
				return previewInput('number', attributes);
			case 'date':
				return previewInput('date', attributes);
			case 'password':
				return previewInput('password', attributes);
			default:
				return previewInput(preview === 'phone' ? 'tel' : 'text', attributes);
		}
	}

	function OptionsEditor(props){
		var options = normalizeOptions(props.options);
		function update(next){
			props.onChange(next);
		}
		return el('div', { className: 'formlayer-editor-options' },
			options.map(function(opt, index){
				return el('div', { key: index, className: 'formlayer-editor-option-row' },
					el(TextControl, {
						value: opt.label,
						onChange: function(val){
							var next = options.slice();
							next[index] = { label: val, value: val, default: !!opt.default };
							update(next);
						}
					}),
					el(Button, {
						isSmall: true,
						isDestructive: true,
						onClick: function(){
							update(options.filter(function(_, i){ return i !== index; }));
						}
					}, __('Remove', 'formlayer'))
				);
			}),
			el(Button, {
				variant: 'secondary',
				onClick: function(){
					update(options.concat([{ label: __('New option', 'formlayer'), value: __('New option', 'formlayer'), default: false }]));
				}
			}, __('Add option', 'formlayer'))
		);
	}

	function extraInspector(def, attributes, setAttributes){
		var type = def.type;
		var extras = [];

		if(['dropdown', 'radio', 'checkbox', 'multiple'].indexOf(type) !== -1){
			extras.push(el(PanelBody, { title: __('Options', 'formlayer'), initialOpen: true },
				el(OptionsEditor, {
					options: attributes.options,
					onChange: function(options){ setAttributes({ options: options }); }
				})
			));
		}

		if(type === 'name'){
			extras.push(el(PanelBody, { title: __('Name Fields', 'formlayer'), initialOpen: false },
				el(ToggleControl, { label: __('First Name', 'formlayer'), checked: attributes.enableFirstName !== false, onChange: function(v){ setAttributes({ enableFirstName: v }); } }),
				el(ToggleControl, { label: __('Middle Name', 'formlayer'), checked: !!attributes.enableMiddleName, onChange: function(v){ setAttributes({ enableMiddleName: v }); } }),
				el(ToggleControl, { label: __('Last Name', 'formlayer'), checked: attributes.enableLastName !== false, onChange: function(v){ setAttributes({ enableLastName: v }); } }),
				el(TextControl, { label: __('First placeholder', 'formlayer'), value: attributes.placeholderFirst || '', onChange: function(v){ setAttributes({ placeholderFirst: v }); } }),
				el(TextControl, { label: __('Last placeholder', 'formlayer'), value: attributes.placeholderLast || '', onChange: function(v){ setAttributes({ placeholderLast: v }); } })
			));
		}

		if(type === 'address'){
			extras.push(el(PanelBody, { title: __('Address Fields', 'formlayer'), initialOpen: false },
				el(ToggleControl, { label: __('Street', 'formlayer'), checked: attributes.enableStreet !== false, onChange: function(v){ setAttributes({ enableStreet: v }); } }),
				el(ToggleControl, { label: __('City', 'formlayer'), checked: attributes.enableCity !== false, onChange: function(v){ setAttributes({ enableCity: v }); } }),
				el(ToggleControl, { label: __('State', 'formlayer'), checked: attributes.enableState !== false, onChange: function(v){ setAttributes({ enableState: v }); } }),
				el(ToggleControl, { label: __('Zip', 'formlayer'), checked: attributes.enableZip !== false, onChange: function(v){ setAttributes({ enableZip: v }); } }),
				el(ToggleControl, { label: __('Country', 'formlayer'), checked: attributes.enableCountry !== false, onChange: function(v){ setAttributes({ enableCountry: v }); } })
			));
		}

		if(type === 'number'){
			extras.push(el(PanelBody, { title: __('Number Limits', 'formlayer'), initialOpen: false },
				el(TextControl, { label: __('Min', 'formlayer'), type: 'number', value: attributes.min || '', onChange: function(v){ setAttributes({ min: v }); } }),
				el(TextControl, { label: __('Max', 'formlayer'), type: 'number', value: attributes.max || '', onChange: function(v){ setAttributes({ max: v }); } })
			));
		}

		if(type === 'textarea' || type === 'richtext'){
			extras.push(el(PanelBody, { title: __('Text Area', 'formlayer'), initialOpen: false },
				el(TextControl, { label: __('Rows', 'formlayer'), type: 'number', value: attributes.rows || 4, onChange: function(v){ setAttributes({ rows: parseInt(v, 10) || 4 }); } })
			));
		}

		if(type === 'captcha'){
			var captchaOptions = [{ label: 'hCaptcha', value: 'hcaptcha' }];
			if(config.is_pro){
				captchaOptions.push({ label: 'Cloudflare Turnstile', value: 'turnstile' });
				captchaOptions.push({ label: 'reCAPTCHA v2', value: 'recaptcha' });
			}
			extras.push(el(PanelBody, { title: __('Captcha', 'formlayer'), initialOpen: false },
				el(SelectControl, {
					label: __('Provider', 'formlayer'),
					value: attributes.captchaProvider || 'hcaptcha',
					options: captchaOptions,
					onChange: function(v){ setAttributes({ captchaProvider: v }); }
				}),
				el(SelectControl, {
					label: __('Theme', 'formlayer'),
					value: attributes.captchaTheme || 'light',
					options: [
						{ label: __('Light', 'formlayer'), value: 'light' },
						{ label: __('Dark', 'formlayer'), value: 'dark' }
					],
					onChange: function(v){ setAttributes({ captchaTheme: v }); }
				})
			));
		}

		if(type === 'submit'){
			extras.push(el(PanelBody, { title: __('Button Style', 'formlayer'), initialOpen: false },
				el(SelectControl, {
					label: __('Alignment', 'formlayer'),
					value: attributes.btnAlign || 'left',
					options: [
						{ label: __('Left', 'formlayer'), value: 'left' },
						{ label: __('Center', 'formlayer'), value: 'center' },
						{ label: __('Right', 'formlayer'), value: 'right' },
						{ label: __('Full width', 'formlayer'), value: 'full' }
					],
					onChange: function(v){ setAttributes({ btnAlign: v }); }
				}),
				el(SelectControl, {
					label: __('Size', 'formlayer'),
					value: attributes.btnSize || 'md',
					options: [
						{ label: __('Small', 'formlayer'), value: 'sm' },
						{ label: __('Medium', 'formlayer'), value: 'md' },
						{ label: __('Large', 'formlayer'), value: 'lg' }
					],
					onChange: function(v){ setAttributes({ btnSize: v }); }
				}),
				el(TextControl, { label: __('Background', 'formlayer'), value: attributes.btnBgColor || '', onChange: function(v){ setAttributes({ btnBgColor: v }); } }),
				el(TextControl, { label: __('Text color', 'formlayer'), value: attributes.btnTextColor || '', onChange: function(v){ setAttributes({ btnTextColor: v }); } }),
				el(TextControl, { label: __('Border radius (px)', 'formlayer'), type: 'number', value: attributes.styleBorderRadius || '', onChange: function(v){ setAttributes({ styleBorderRadius: v }); } })
			));
		}

		if(type === 'gdpr'){
			extras.push(el(PanelBody, { title: __('GDPR Copy', 'formlayer'), initialOpen: false },
				el(TextControl, { label: __('GDPR label', 'formlayer'), value: attributes.gdprLabel || '', onChange: function(v){ setAttributes({ gdprLabel: v }); } }),
				el(TextareaControl, { label: __('Description', 'formlayer'), value: attributes.gdprDescription || '', onChange: function(v){ setAttributes({ gdprDescription: v }); } })
			));
		}

		if(type === 'terms'){
			extras.push(el(PanelBody, { title: __('Terms Copy', 'formlayer'), initialOpen: false },
				el(TextareaControl, { label: __('Terms label', 'formlayer'), value: attributes.termsLabel || '', onChange: function(v){ setAttributes({ termsLabel: v }); } })
			));
		}

		return extras;
	}

	function FieldToolbar(props){
		var clientId = props.clientId;
		var dispatch = useDispatch ? useDispatch('core/block-editor') : null;
		var info = useSelect ? useSelect(function(select){
			var store = select('core/block-editor');
			if(!store){
				return { index: 0, total: 0 };
			}
			var order = store.getBlockOrder() || [];
			return {
				index: order.indexOf(clientId),
				total: order.length
			};
		}, [clientId]) : { index: 0, total: 0 };

		if(!dispatch || !clientId){
			return null;
		}

		function stop(e){
			e.preventDefault();
			e.stopPropagation();
		}

		return el('div', {
			className: 'formlayer-field-actions',
			onMouseDown: function(e){ e.stopPropagation(); },
			onClick: stop
		},
			el('button', {
				type: 'button',
				title: __('Move Up', 'formlayer'),
				disabled: info.index <= 0,
				onClick: function(e){
					stop(e);
					if(dispatch.moveBlocksUp){
						dispatch.moveBlocksUp([clientId]);
					}
				}
			}, el('span', { className: 'dashicons dashicons-arrow-up-alt2' })),
			el('button', {
				type: 'button',
				title: __('Move Down', 'formlayer'),
				disabled: info.index < 0 || info.index >= info.total - 1,
				onClick: function(e){
					stop(e);
					if(dispatch.moveBlocksDown){
						dispatch.moveBlocksDown([clientId]);
					}
				}
			}, el('span', { className: 'dashicons dashicons-arrow-down-alt2' })),
			el('button', {
				type: 'button',
				title: __('Clone', 'formlayer'),
				onClick: function(e){
					stop(e);
					if(dispatch.duplicateBlocks){
						dispatch.duplicateBlocks([clientId]);
						return;
					}
					var block = wp.data.select('core/block-editor').getBlock(clientId);
					if(block && cloneBlock && dispatch.insertBlock){
						dispatch.insertBlock(cloneBlock(block), info.index + 1);
					}
				}
			}, el('span', { className: 'dashicons dashicons-admin-page' })),
			el('button', {
				type: 'button',
				className: 'formlayer-field-delete',
				title: __('Delete', 'formlayer'),
				onClick: function(e){
					stop(e);
					if(dispatch.removeBlock){
						dispatch.removeBlock(clientId);
					}
				}
			}, el('span', { className: 'dashicons dashicons-trash' }))
		);
	}

	function editField(props, def){
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var hideLabel = ['submit', 'section', 'hidden', 'terms', 'gdpr'].indexOf(def.type) !== -1 || attributes.labelPlacement === 'hidden';
		var isSelected = Boolean(props.isSelected);

		var wrapClass = [
			'formlayer-form',
			'formlayer-field-wrap',
			'formlayer-editor-field',
			'formlayer-field-instance',
			isSelected ? 'active is-selected' : '',
			'label-' + (attributes.labelPlacement || 'top'),
			attributes.fieldWidth === 50 ? 'formlayer-width-50' : '',
			attributes.containerClass || ''
		].filter(Boolean).join(' ').trim();

		var labelStyle = {};
		if(attributes.styleLabelColor){
			labelStyle.color = attributes.styleLabelColor;
		}

		return el(Fragment, {},
			el(InspectorControls, {},
				el(PanelBody, { title: __('Field Settings', 'formlayer'), initialOpen: true },
					el(TextControl, {
						label: __('Label', 'formlayer'),
						value: attributes.label || '',
						onChange: function(v){ setAttributes({ label: v }); }
					}),
					def.type !== 'submit' ? el(ToggleControl, {
						label: __('Required', 'formlayer'),
						checked: !!attributes.required,
						onChange: function(v){ setAttributes({ required: v }); }
					}) : null,
					['submit', 'section', 'hidden', 'captcha', 'rating', 'gdpr', 'terms', 'checkbox', 'radio', 'multiple', 'dropdown'].indexOf(def.type) === -1 ? el(TextControl, {
						label: __('Placeholder', 'formlayer'),
						value: attributes.placeholder || '',
						onChange: function(v){ setAttributes({ placeholder: v }); }
					}) : null,
					el(SelectControl, {
						label: __('Width', 'formlayer'),
						value: String(attributes.fieldWidth || 100),
						options: [
							{ label: __('Full', 'formlayer'), value: '100' },
							{ label: __('Half', 'formlayer'), value: '50' }
						],
						onChange: function(v){ setAttributes({ fieldWidth: parseInt(v, 10) }); }
					}),
					def.type !== 'submit' && def.type !== 'hidden' ? el(SelectControl, {
						label: __('Label placement', 'formlayer'),
						value: attributes.labelPlacement || 'top',
						options: [
							{ label: __('Top', 'formlayer'), value: 'top' },
							{ label: __('Left', 'formlayer'), value: 'left' },
							{ label: __('Right', 'formlayer'), value: 'right' },
							{ label: __('Hidden', 'formlayer'), value: 'hidden' }
						],
						onChange: function(v){ setAttributes({ labelPlacement: v }); }
					}) : null,
					el(TextareaControl, {
						label: __('Help text', 'formlayer'),
						value: attributes.helpText || '',
						onChange: function(v){ setAttributes({ helpText: v }); }
					})
				),
				extraInspector(def, attributes, setAttributes),
				el(PanelBody, { title: __('Advanced', 'formlayer'), initialOpen: false },
					el(TextControl, {
						label: __('Name / merge tag', 'formlayer'),
						help: '{field_name} in emails',
						value: attributes.nameAttr || '',
						onChange: function(v){ setAttributes({ nameAttr: slugify(v) }); }
					}),
					['submit', 'section', 'terms', 'gdpr', 'captcha'].indexOf(def.type) === -1 ? el(TextControl, {
						label: __('Default value', 'formlayer'),
						value: attributes.defaultValue || '',
						onChange: function(v){ setAttributes({ defaultValue: v }); }
					}) : null,
					el(TextControl, {
						label: __('CSS class', 'formlayer'),
						value: attributes.containerClass || '',
						onChange: function(v){ setAttributes({ containerClass: v }); }
					}),
					el(TextControl, {
						label: __('Label color', 'formlayer'),
						value: attributes.styleLabelColor || '',
						onChange: function(v){ setAttributes({ styleLabelColor: v }); }
					})
				)
			),
			el('div', {
				className: wrapClass,
				onClick: function(e){
					if(wp.data && wp.data.dispatch){
						var bedit = wp.data.dispatch('core/block-editor');
						if(bedit && bedit.selectBlock){
							bedit.selectBlock(props.clientId);
						}
					}
				}
			},
				el(FieldToolbar, { clientId: props.clientId }),
				!hideLabel ? el('div', { className: 'formlayer-editor-label-row' },
					el(RichText, {
						tagName: 'span',
						className: 'formlayer-label',
						value: attributes.label || '',
						placeholder: def.title || __('Field label', 'formlayer'),
						onChange: function(label){ setAttributes({ label: label }); },
						allowedFormats: [],
						style: labelStyle
					}),
					attributes.required ? el('span', { className: 'required-indicator' }, '*') : null
				) : null,
				def.type === 'submit' ? null : previewFieldControl(def, attributes),
				def.type === 'submit' ? el('div', { className: 'formlayer-submit-wrap align-' + (attributes.btnAlign || 'left') }, previewFieldControl(def, attributes)) : null,
				attributes.helpText && def.type !== 'section' ? el('div', { className: 'formlayer-help-text' }, attributes.helpText) : null
			)
		);
	}

	blockDefs.forEach(function(def){
		if(!def || !def.name){
			return;
		}
		registerBlockType(def.name, {
			apiVersion: 3,
			title: def.title,
			description: def.title,
			icon: def.icon || 'feedback',
			category: 'formlayer-fields',
			keywords: def.keywords || [],
			supports: {
				html: false,
				reusable: false,
				customClassName: true,
				anchor: false
			},
			attributes: def.attributes || {},
			edit: function(props){
				var attributes = props.attributes;
				var setAttributes = props.setAttributes;
				useEffect(function(){
					var next = {};
					if(!attributes.fieldId){
						next.fieldId = 'f' + Date.now().toString(36) + Math.floor(Math.random() * 1000);
					}
					if(!attributes.label && def.title){
						next.label = def.title;
					}
					if(!attributes.nameAttr && (attributes.label || def.title)){
						next.nameAttr = slugify(attributes.label || def.title);
					}
					if(Object.keys(next).length){
						setAttributes(next);
					}
				}, []);
				return editField(props, def);
			},
			save: function(){
				return null;
			}
		});
	});

	function insertFieldBlock(def, atIndex){
		if(!wp.data || !createBlock || !def){
			return;
		}
		var dispatch = wp.data.dispatch('core/block-editor');
		var select = wp.data.select('core/block-editor');
		if(!dispatch || !select){
			return;
		}
		var blocks = select.getBlocks() || [];
		
		// Find submit button index if present
		var submitIndex = -1;
		blocks.forEach(function(block, i){
			if(block.name === 'formlayer/submit-button'){
				submitIndex = i;
			}
		});

		var targetIndex;
		if(typeof atIndex === 'number' && atIndex >= 0 && atIndex <= blocks.length){
			targetIndex = atIndex;
			// If inserting at or past submit button, and this is not the submit button itself, place before submit button
			if(submitIndex !== -1 && def.name !== 'formlayer/submit-button' && targetIndex >= submitIndex){
				targetIndex = submitIndex;
			}
		} else {
			// Default / no index / drop below: append at the bottom (bellow existing fields, before submit button if present)
			if(submitIndex !== -1 && def.name !== 'formlayer/submit-button'){
				targetIndex = submitIndex;
			} else {
				targetIndex = blocks.length;
			}
		}

		if(targetIndex < 0){
			targetIndex = 0;
		}
		if(targetIndex > blocks.length){
			targetIndex = blocks.length;
		}

		var attrs = {
			label: def.title,
			fieldId: 'f' + Date.now().toString(36) + Math.floor(Math.random() * 1000),
			nameAttr: slugify(def.title)
		};
		dispatch.insertBlock(createBlock(def.name, attrs), targetIndex);
	}

	function getDropIndex(clientY){
		var iframe = getEditorIframe();
		var offsetTop = 0;
		var doc = document;
		if(iframe && iframe.contentDocument){
			doc = iframe.contentDocument;
			try{
				var ifRect = iframe.getBoundingClientRect();
				offsetTop = ifRect.top;
			}catch(e){}
		}

		var rawNodes = doc.querySelectorAll('.is-root-container > .wp-block, .is-root-container > .block-editor-block-list__block, .is-root-container > [data-block]');
		var nodes = [];
		for(var k = 0; k < rawNodes.length; k++){
			var n = rawNodes[k];
			if(!n.classList.contains('block-list-appender') && !n.classList.contains('block-editor-default-block-appender') && !n.classList.contains('editor-visual-editor__post-title-wrapper') && !n.classList.contains('edit-post-visual-editor__post-title-wrapper')){
				nodes.push(n);
			}
		}

		if(!nodes.length || typeof clientY !== 'number' || isNaN(clientY)){
			return -1; // -1 indicates appending below existing fields
		}

		// Check if dropped clearly above the first field
		var firstRect = nodes[0].getBoundingClientRect();
		if(clientY < offsetTop + firstRect.top + (firstRect.height * 0.35)){
			return 0;
		}

		for(var i = 0; i < nodes.length; i++){
			var rect = nodes[i].getBoundingClientRect();
			var top = offsetTop + rect.top;
			var mid = top + (rect.height * 0.5);
			var bottom = top + rect.height;

			if(clientY >= top && clientY <= bottom){
				return clientY < mid ? i : (i + 1);
			}
			if(i < nodes.length - 1){
				var nextRect = nodes[i + 1].getBoundingClientRect();
				var nextTop = offsetTop + nextRect.top;
				if(clientY > bottom && clientY < nextTop){
					return i + 1;
				}
			}
		}

		// Dropped below all nodes
		return nodes.length;
	}

	function defFromDragEvent(e){
		var name = '';
		try{
			name = e && e.dataTransfer ? (e.dataTransfer.getData('formlayer/field') || e.dataTransfer.getData('text/plain')) : '';
		}catch(err){
			name = '';
		}
		if(!name && window._formlayer_dragged_def){
			return window._formlayer_dragged_def;
		}
		if(!name){
			return null;
		}
		for(var i = 0; i < blockDefs.length; i++){
			if(blockDefs[i].name === name || blockDefs[i].type === name){
				return blockDefs[i];
			}
		}
		return window._formlayer_dragged_def || null;
	}

	function ensureLeftSidebarRoot(){
		var body = document.querySelector('.interface-interface-skeleton__body');
		if(!body){
			return null;
		}
		var root = document.getElementById('formlayer-left-sidebar-root');
		if(!root){
			root = document.createElement('aside');
			root.id = 'formlayer-left-sidebar-root';
			root.className = 'formlayer-left-sidebar';
			body.insertBefore(root, body.firstChild);
			document.body.classList.add('formlayer-has-left-sidebar');
		}
		return root;
	}

	function FieldsPalette(){
		var searchState = useState('');
		var search = searchState[0];
		var setSearch = searchState[1];
		var collapseState = useState(false);
		var collapsed = collapseState[0];
		var setCollapsed = collapseState[1];
		var cats = config.categories || [];
		var teasers = config.teasers || [];

		useEffect(function(){
			var root = document.getElementById('formlayer-left-sidebar-root');
			if(root){
				if(collapsed){
					root.classList.add('is-collapsed');
				}else{
					root.classList.remove('is-collapsed');
				}
			}
		}, [collapsed]);

		function matches(item){
			if(!search){
				return true;
			}
			var q = search.toLowerCase();
			return String(item.title || item.type || '').toLowerCase().indexOf(q) !== -1;
		}

		var logoUrl = config.logo_icon_url || config.logo_url || '';

		return el('div', {
			className: 'formlayer-left-sidebar-inner',
			onClick: collapsed ? function(){ setCollapsed(false); } : undefined
		},
			el('div', {
				className: 'formlayer-left-sidebar-header',
				title: collapsed ? __('Click to expand fields', 'formlayer') : undefined
			},
				logoUrl ? el('img', {
					src: logoUrl,
					alt: 'FormLayer',
					className: 'formlayer-left-sidebar-logo',
					onClick: function(e){
						if(collapsed){
							e.stopPropagation();
							setCollapsed(false);
						}
					}
				}) : el('span', { className: 'formlayer-left-sidebar-mark' }),
				el('div', { className: 'formlayer-left-sidebar-heading' },
					el('strong', {}, __('FormLayer', 'formlayer')),
					el('span', {}, __('Form Fields', 'formlayer'))
				),
				el('button', {
					type: 'button',
					className: 'formlayer-left-sidebar-toggle',
					title: collapsed ? __('Expand fields', 'formlayer') : __('Collapse fields', 'formlayer'),
					onClick: function(e){
						e.stopPropagation();
						setCollapsed(!collapsed);
					}
				}, el('span', { className: 'dashicons dashicons-' + (collapsed ? 'arrow-right-alt2' : 'arrow-left-alt2') }))
			),
			collapsed ? null : el('div', { className: 'formlayer-editor-palette' },
				el('p', { className: 'formlayer-editor-palette-help' }, __('Drag a field onto the canvas, or click it to add.', 'formlayer')),
				el(TextControl, {
					placeholder: __('Search fields…', 'formlayer'),
					value: search,
					onChange: setSearch
				}),
				cats.map(function(cat){
					var items = blockDefs.filter(function(def){ return def.category === cat.id && matches(def); });
					var locked = teasers.filter(function(t){ return t.category === cat.id && matches(t); });
					if(!items.length && !locked.length){
						return null;
					}
					return el('div', { key: cat.id, className: 'formlayer-editor-cat' },
						el('h3', {}, cat.label),
						el('div', { className: 'formlayer-editor-field-grid' },
							items.map(function(def){
								return el('button', {
									key: def.name,
									type: 'button',
									className: 'formlayer-editor-field-btn',
									draggable: true,
									onClick: function(){ insertFieldBlock(def); },
									onDragStart: function(e){
										window._formlayer_dragged_def = def;
										if(!e.dataTransfer){
											return;
										}
										e.dataTransfer.effectAllowed = 'copy';
										e.dataTransfer.setData('formlayer/field', def.name);
										e.dataTransfer.setData('text/plain', def.name);
										document.body.classList.add('formlayer-dragging-field');
									},
									onDragEnd: function(){
										window._formlayer_dragged_def = null;
										document.body.classList.remove('formlayer-dragging-field');
									}
								},
									el('span', { className: 'dashicons dashicons-move formlayer-field-move-icon' }),
									el('span', { className: 'dashicons dashicons-' + (def.icon || 'editor-textcolor') }),
									el('span', {}, def.title)
								);
							}),
							locked.map(function(t){
								return el('button', {
									key: t.type,
									type: 'button',
									className: 'formlayer-editor-field-btn is-locked',
									disabled: true,
									title: __('Available in FormLayer Pro', 'formlayer')
								},
									el('span', { className: 'dashicons dashicons-lock' }),
									el('span', {}, t.title)
								);
							})
						)
					);
				})
			)
		);
	}

	function FormLayerLeftDock(){
		var readyState = useState(false);
		var ready = readyState[0];
		var setReady = readyState[1];

		useEffect(function(){
			var tries = 0;
			var timer = setInterval(function(){
				tries++;
				if(ensureLeftSidebarRoot() || tries > 50){
					clearInterval(timer);
					setReady(true);
				}
			}, 200);
			return function(){
				clearInterval(timer);
			};
		}, []);

		var root = document.getElementById('formlayer-left-sidebar-root') || ensureLeftSidebarRoot();
		if(!ready || !root || !wp.element.createPortal){
			return null;
		}
		return wp.element.createPortal(el(FieldsPalette, null), root);
	}

	function CanvasDropOverlay(){
		var dragState = useState(false);
		var dragging = dragState[0];
		var setDragging = dragState[1];

		useEffect(function(){
			function onStart(e){
				if(!e.dataTransfer){
					return;
				}
				var types = e.dataTransfer.types ? Array.prototype.slice.call(e.dataTransfer.types) : [];
				if(types.indexOf('formlayer/field') !== -1 || document.body.classList.contains('formlayer-dragging-field') || window._formlayer_dragged_def){
					setDragging(true);
				}
			}
			function onEnd(){
				setDragging(false);
				window._formlayer_dragged_def = null;
				document.body.classList.remove('formlayer-dragging-field');
			}
			document.addEventListener('dragstart', onStart);
			document.addEventListener('dragend', onEnd);
			document.addEventListener('drop', onEnd);
			return function(){
				document.removeEventListener('dragstart', onStart);
				document.removeEventListener('dragend', onEnd);
				document.removeEventListener('drop', onEnd);
			};
		}, []);

		if(!dragging){
			return null;
		}

		var overlay = el('div', {
			className: 'formlayer-canvas-drop-overlay',
			onDragOver: function(e){
				e.preventDefault();
				if(e.dataTransfer){
					e.dataTransfer.dropEffect = 'copy';
				}
			},
			onDrop: function(e){
				e.preventDefault();
				e.stopPropagation();
				var def = defFromDragEvent(e) || window._formlayer_dragged_def;
				if(def){
					insertFieldBlock(def, getDropIndex(e.clientY));
				}
				window._formlayer_dragged_def = null;
				setDragging(false);
				document.body.classList.remove('formlayer-dragging-field');
			}
		}, el('span', {}, __('Drop field here', 'formlayer')));

		var target = document.querySelector('.editor-visual-editor, .interface-interface-skeleton__content');
		if(target && wp.element.createPortal){
			return wp.element.createPortal(overlay, target);
		}
		return overlay;
	}

	function parseSettings(raw){
		var defaults = JSON.parse(JSON.stringify(config.default_settings || {
			notifications: {
				enabled: true,
				to_email: '{admin_email}',
				reply_to: '',
				from_name: 'FormLayer',
				from_email: '{admin_email}',
				bcc: '',
				subject: 'New Form Submission',
				message: 'You have a new submission:\n\n{all_fields}',
				format: 'html'
			},
			email_confirmation: {
				enabled: false,
				to_email: '',
				reply_to: '{admin_email}',
				from_name: 'FormLayer',
				from_email: '{admin_email}',
				bcc: '',
				subject: 'Thank you for your submission!',
				message: 'Thank you! We have received your submission:\n\n{all_fields}',
				format: 'html'
			},
			confirmations: {
				type: 'message',
				message: 'Thank you for your submission!',
				redirect_url: '',
				hide_form: true
			},
			integrations: {},
			custom_css: ''
		}));

		var res = {};
		if(!raw){
			res = defaults;
		} else if(typeof raw === 'object' && raw !== null){
			res = Object.assign({}, defaults, raw);
		} else {
			try{
				var parsed = JSON.parse(raw);
				res = (parsed && typeof parsed === 'object') ? Object.assign({}, defaults, parsed) : defaults;
			}catch(e){
				res = defaults;
			}
		}
		if(!res.notifications || typeof res.notifications !== 'object'){
			res.notifications = Object.assign({}, defaults.notifications);
		}
		if(!res.email_confirmation || typeof res.email_confirmation !== 'object'){
			res.email_confirmation = Object.assign({}, defaults.email_confirmation);
		}
		if(!res.confirmations || typeof res.confirmations !== 'object'){
			res.confirmations = Object.assign({}, defaults.confirmations);
		}
		if(!res.integrations || typeof res.integrations !== 'object' || Array.isArray(res.integrations)){
			res.integrations = {};
		}
		return res;
	}

	function ensureHeaderButtonRoot(){
		var header = document.querySelector('.editor-header__settings, .edit-post-header__settings');
		if(!header){
			return null;
		}
		var root = document.getElementById('formlayer-header-settings-root');
		if(!root){
			root = document.createElement('div');
			root.id = 'formlayer-header-settings-root';
			root.className = 'formlayer-header-settings-root';
			header.insertBefore(root, header.firstChild);
		}
		return root;
	}

	function FormSettingsApp(){
		if(!useEntityProp && !useSelect){
			return null;
		}
		var openState = useState(false);
		var open = openState[0];
		var setOpen = openState[1];
		var sectionState = useState('notifications');
		var section = sectionState[0] || 'notifications';
		var setSection = sectionState[1];
		var headerReadyState = useState(false);
		var headerReady = headerReadyState[0];
		var setHeaderReady = headerReadyState[1];
		var copiedState = useState(false);
		var copied = copiedState[0];
		var setCopied = copiedState[1];
		var metaState = useEntityProp ? useEntityProp('postType', 'formlayer_form', 'meta') : [{}, function(){}];
		var meta = (metaState && metaState[0]) || {};
		var setMeta = metaState && metaState[1];

		var editedMeta = useSelect ? useSelect(function(select){
			var editor = select('core/editor');
			if(editor && editor.getEditedPostAttribute){
				return editor.getEditedPostAttribute('meta') || {};
			}
			return {};
		}, []) : {};

		var currentMeta = Object.assign({}, meta, editedMeta);
		var settings = parseSettings(currentMeta._formlayer_form_settings);
		var displayId = currentMeta._formlayer_display_id || config.display_id || 0;
		var shortcode = displayId ? '[formlayer id="' + displayId + '"]' : (config.shortcode || '');
		var notifications = settings.notifications || {};
		var confirmationMail = settings.email_confirmation || {};
		var confirmations = settings.confirmations || {};

		useEffect(function(){
			var tries = 0;
			var timer = setInterval(function(){
				tries++;
				if(ensureHeaderButtonRoot() || tries > 50){
					clearInterval(timer);
					setHeaderReady(true);
				}
			}, 200);
			return function(){
				clearInterval(timer);
			};
		}, []);

		function updateSettings(next){
			var json = JSON.stringify(next);
			var newMeta = Object.assign({}, meta, editedMeta, {
				_formlayer_form_settings: json
			});
			if(setMeta && typeof setMeta === 'function'){
				setMeta(newMeta);
			}
			if(wp.data && wp.data.dispatch){
				var editorDispatch = wp.data.dispatch('core/editor');
				if(editorDispatch && typeof editorDispatch.editPost === 'function'){
					editorDispatch.editPost({ meta: newMeta });
				}
			}
		}

		function patch(group, key, value){
			var next = parseSettings(currentMeta._formlayer_form_settings);
			if(!next[group] || typeof next[group] !== 'object' || Array.isArray(next[group])){
				next[group] = {};
			}
			next[group][key] = value;
			updateSettings(next);
		}

		function patchRoot(key, value){
			var next = parseSettings(currentMeta._formlayer_form_settings);
			next[key] = value;
			updateSettings(next);
		}

		function navItem(id, icon, label){
			var isActive = (section === id);
			return el('button', {
				type: 'button',
				key: id,
				className: 'formlayer-settings-nav-item' + (isActive ? ' is-active' : ''),
				onClick: function(){ setSection(id); }
			},
				el('span', { className: 'dashicons dashicons-' + icon }),
				el('span', {}, label)
			);
		}

		function fieldRow(label, control){
			return el('div', { className: 'formlayer-settings-field' },
				el('label', {}, label),
				control
			);
		}

		function getAvailableMergeTags(){
			var tags = [
				{ tag: '{all_fields}', label: '{all_fields}' },
				{ tag: '{admin_email}', label: '{admin_email}' },
				{ tag: '{form_title}', label: '{form_title}' },
				{ tag: '{site_url}', label: '{site_url}' }
			];
			if(wp.data && wp.data.select){
				var blockEditor = wp.data.select('core/block-editor');
				if(blockEditor && typeof blockEditor.getBlocks === 'function'){
					var blocks = blockEditor.getBlocks();
					if(blocks && blocks.length){
						blocks.forEach(function(b){
							var attrs = b.attrs || {};
							var blockType = b.name ? b.name.replace('formlayer/', '').replace('-field', '') : '';
							if(['submit', 'section', 'gdpr', 'terms'].indexOf(blockType) !== -1){
								return;
							}
							var name = attrs.nameAttr || attrs.fieldId || (attrs.label ? slugify(attrs.label) : '');
							if(name){
								tags.push({ tag: '{' + name + '}', label: '{' + name + '}' });
							}
						});
					}
				}
			}
			return tags;
		}

		function renderMergeTags(group, fieldProp){
			var tags = getAvailableMergeTags();
			return el('div', {
				className: 'formlayer-merge-tags-wrapper'
			},
				el('div', {
					style: { fontSize: '12px', fontWeight: '600', marginBottom: '8px', color: '#475569' }
				}, __('Available Merge Tags (Click to insert)', 'formlayer')),
				el('div', {
					id: 'formlayer-dynamic-merge-tags',
					className: 'formlayer-dynamic-merge-tags',
					style: { display: 'flex', flexWrap: 'wrap', gap: '6px' }
				},
					tags.map(function(t){
						return el('span', {
							key: t.tag,
							className: 'formlayer-badge-tag',
							onClick: function(){
								var currentVal = (group === 'email_confirmation' ? confirmationMail : notifications)[fieldProp || 'message'] || '';
								patch(group, fieldProp || 'message', currentVal + t.tag);
							}
						}, t.label);
					})
				)
			);
		}

		function emailFields(group, data){
			var isEnabled = data.enabled !== false;
			return el(Fragment, {},
				el('div', { className: 'formlayer-settings-field' },
					el('label', {}, __('Enabled', 'formlayer')),
					el('div', { className: 'formlayer-switch-wrapper' },
						el('label', { className: 'formlayer-switch' },
							el('input', {
								type: 'checkbox',
								checked: isEnabled,
								onChange: function(e){ patch(group, 'enabled', Boolean(e.target.checked)); }
							}),
							el('span', { className: 'slider round' })
						)
					)
				),
				el('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' } },
					el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('Send To Email', 'formlayer')),
						el('input', {
							type: 'text',
							className: 'formlayer-input',
							placeholder: '{admin_email}',
							value: data.to_email || '',
							onChange: function(e){ patch(group, 'to_email', e.target.value); }
						})
					),
					el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('Reply To', 'formlayer')),
						el('input', {
							type: 'text',
							className: 'formlayer-input',
							placeholder: group === 'email_confirmation' ? '{admin_email}' : 'e.g. {field_email}',
							value: data.reply_to || '',
							onChange: function(e){ patch(group, 'reply_to', e.target.value); }
						})
					)
				),
				el('div', { style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' } },
					el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('From Name', 'formlayer')),
						el('input', {
							type: 'text',
							className: 'formlayer-input',
							placeholder: 'FormLayer',
							value: data.from_name || '',
							onChange: function(e){ patch(group, 'from_name', e.target.value); }
						})
					),
					el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('From Email', 'formlayer')),
						el('input', {
							type: 'text',
							className: 'formlayer-input',
							placeholder: '{admin_email}',
							value: data.from_email || '',
							onChange: function(e){ patch(group, 'from_email', e.target.value); }
						})
					)
				),
				el('div', { className: 'formlayer-settings-field' },
					el('label', {}, __('BCC', 'formlayer')),
					el('input', {
						type: 'text',
						className: 'formlayer-input',
						placeholder: __('comma separated emails', 'formlayer'),
						value: data.bcc || '',
						onChange: function(e){ patch(group, 'bcc', e.target.value); }
					})
				),
				el('div', { className: 'formlayer-settings-field' },
					el('label', {}, __('Subject', 'formlayer')),
					el('input', {
						type: 'text',
						className: 'formlayer-input',
						placeholder: group === 'email_confirmation' ? 'Thank you for your submission!' : 'New Form Submission',
						value: data.subject || '',
						onChange: function(e){ patch(group, 'subject', e.target.value); }
					})
				),
				el('div', { className: 'formlayer-settings-field' },
					el('label', {}, __('Message Format', 'formlayer')),
					el('select', {
						className: 'formlayer-select',
						value: data.format || 'html',
						onChange: function(e){ patch(group, 'format', e.target.value); }
					},
						el('option', { value: 'html' }, __('HTML (Recommended)', 'formlayer')),
						el('option', { value: 'plain' }, __('Plain Text', 'formlayer'))
					)
				),
				el('div', { className: 'formlayer-settings-field' },
					el('label', { style: { display: 'flex', justifyContent: 'space-between' } },
						el('span', {}, __('Message Body', 'formlayer'))
					),
					el('textarea', {
						className: 'formlayer-input',
						style: { height: '120px' },
						value: data.message || '',
						onChange: function(e){ patch(group, 'message', e.target.value); }
					}),
					renderMergeTags(group, 'message')
				)
			);
		}

		function proLock(title, text){
			return el('div', { className: 'formlayer-pro-lock-pane formlayer-pro-lock-pane-wrap' },
				el('div', { className: 'formlayer-pro-lock-icon-box' },
					el('span', { className: 'dashicons dashicons-lock', style: { fontSize: '32px', height: '32px', width: '32px', color: '#5525d6' } })
				),
				el('h4', { className: 'formlayer-empty-title', style: { margin: '0 0 8px 0', fontSize: '18px', fontWeight: '700', color: '#1e293b' } }, title),
				el('p', { style: { color: '#64748b', margin: '0 0 20px 0', fontSize: '14px' } }, text),
				el('a', { href: 'https://wpformlayer.com/pricing', target: '_blank', className: 'formlayer-btn formlayer-btn-primary' }, __('Go Pro', 'formlayer'))
			);
		}

		function patchIntegration(service, key, value){
			var next = parseSettings(currentMeta._formlayer_form_settings);
			if(!next.integrations || typeof next.integrations !== 'object' || Array.isArray(next.integrations)){
				next.integrations = {};
			}
			next.integrations[service] = Object.assign({}, next.integrations[service] || {});
			next.integrations[service][key] = value;
			updateSettings(next);
		}

		function renderIntegrationCard(slug, title, logo, fieldsFn){
			var integrationsObj = (settings && typeof settings.integrations === 'object' && !Array.isArray(settings.integrations)) ? settings.integrations : {};
			var intData = integrationsObj[slug] || {};
			var isEnabled = Boolean(intData.enabled);
			var logoUrl = config.pro_assets_url ? config.pro_assets_url + '/img/' + logo : '';

			return el('div', { key: slug, className: 'formlayer-integration-setting-card' + (isEnabled ? ' is-enabled' : '') },
				el('div', { className: 'formlayer-integration-card-header' },
					el('div', { className: 'formlayer-integration-card-info' },
						logoUrl ? el('img', { src: logoUrl, alt: title, className: 'formlayer-integration-logo' }) : el('span', { className: 'dashicons dashicons-admin-plugins' }),
						el('span', { className: 'formlayer-integration-card-title' }, title)
					),
					el('div', { className: 'formlayer-switch-wrapper' },
						el('label', { className: 'formlayer-switch' },
							el('input', {
								type: 'checkbox',
								checked: isEnabled,
								onChange: function(e){
									patchIntegration(slug, 'enabled', Boolean(e.target.checked));
								}
							}),
							el('span', { className: 'slider round' })
						)
					)
				),
				isEnabled ? el('div', { className: 'formlayer-integration-card-body' }, fieldsFn(intData)) : null
			);
		}

		function integrationsList(){
			return el('div', { className: 'formlayer-integrations-list' },
				renderIntegrationCard('slack', 'Slack', 'slack-logo.png', function(data){
					return el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('Override Webhook URL', 'formlayer')),
						el('input', {
							type: 'text',
							className: 'formlayer-input',
							value: data.webhook || '',
							placeholder: 'https://hooks.slack.com/services/...',
							onChange: function(e){ patchIntegration('slack', 'webhook', e.target.value); }
						})
					);
				}),
				renderIntegrationCard('sheets', 'Google Sheets', 'sheets-logo.png', function(data){
					return el(Fragment, {},
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Spreadsheet ID', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.spreadsheet_id || data.sheets_id || '',
								placeholder: 'e.g. 1a2b3c4d...',
								onChange: function(e){ patchIntegration('sheets', 'spreadsheet_id', e.target.value); }
							})
						),
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Sheet Name', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.sheet_name || '',
								placeholder: 'e.g. Sheet1',
								onChange: function(e){ patchIntegration('sheets', 'sheet_name', e.target.value); }
							})
						)
					);
				}),
				renderIntegrationCard('mailchimp', 'Mailchimp', 'mailchimp-logo.png', function(data){
					return el(Fragment, {},
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Override Audience ID', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.list_id || data.list || '',
								placeholder: __('Leave empty to use global list', 'formlayer'),
								onChange: function(e){ patchIntegration('mailchimp', 'list_id', e.target.value); }
							})
						),
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Override API Key', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.api_key || '',
								placeholder: __('Leave empty to use global API key', 'formlayer'),
								onChange: function(e){ patchIntegration('mailchimp', 'api_key', e.target.value); }
							})
						)
					);
				}),
				renderIntegrationCard('notion', 'Notion', 'notion-logo.png', function(data){
					return el(Fragment, {},
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Override Database ID', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.database_id || data.db || '',
								placeholder: __('Leave empty to use global database', 'formlayer'),
								onChange: function(e){ patchIntegration('notion', 'database_id', e.target.value); }
							})
						),
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Internal Integration Token', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.api_key || '',
								placeholder: __('Leave empty to use global token', 'formlayer'),
								onChange: function(e){ patchIntegration('notion', 'api_key', e.target.value); }
							})
						)
					);
				}),
				renderIntegrationCard('trello', 'Trello', 'trello-logo.png', function(data){
					return el(Fragment, {},
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('List ID', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.list_id || data.list || '',
								placeholder: __('Enter Trello List ID', 'formlayer'),
								onChange: function(e){ patchIntegration('trello', 'list_id', e.target.value); }
							})
						),
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Override API Key', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.api_key || '',
								placeholder: __('Leave empty to use global API key', 'formlayer'),
								onChange: function(e){ patchIntegration('trello', 'api_key', e.target.value); }
							})
						),
						el('div', { className: 'formlayer-settings-field' },
							el('label', {}, __('Override Token', 'formlayer')),
							el('input', {
								type: 'text',
								className: 'formlayer-input',
								value: data.token || '',
								placeholder: __('Leave empty to use global token', 'formlayer'),
								onChange: function(e){ patchIntegration('trello', 'token', e.target.value); }
							})
						)
					);
				}),
				renderIntegrationCard('discord', 'Discord', 'discord-logo.png', function(data){
					return el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('Override Webhook URL', 'formlayer')),
						el('input', {
							type: 'text',
							className: 'formlayer-input',
							value: data.webhook || '',
							placeholder: 'https://discord.com/api/webhooks/...',
							onChange: function(e){ patchIntegration('discord', 'webhook', e.target.value); }
						})
					);
				})
			);
		}

		var sections = {
			notifications: el('div', { className: 'formlayer-settings-section active', id: 'formlayer-settings-notifications' },
				el('div', { className: 'section-header' },
					el('h3', {}, __('Email Notifications', 'formlayer')),
					el('p', {}, __('Configure how you want to be notified when a form is submitted.', 'formlayer'))
				),
				emailFields('notifications', notifications)
			),
			email_confirmation: el('div', { className: 'formlayer-settings-section active', id: 'formlayer-settings-email_confirmation' },
				el('div', { className: 'section-header' },
					el('h3', {}, __('Email Confirmation', 'formlayer')),
					el('p', {}, __('Send an automatic confirmation email to the person who submitted the form.', 'formlayer'))
				),
				config.is_pro ? emailFields('email_confirmation', confirmationMail) : proLock(
					__('Unlock Email Confirmations', 'formlayer'),
					__('Automatically send a confirmation email to your users after they submit a form with FormLayer Pro.', 'formlayer')
				)
			),
			confirmations: el('div', { className: 'formlayer-settings-section active', id: 'formlayer-settings-confirmations' },
				el('div', { className: 'section-header' },
					el('h3', {}, __('Form Confirmations', 'formlayer')),
					el('p', {}, __('What happens after a user submits the form?', 'formlayer'))
				),
				el('div', { className: 'formlayer-settings-field' },
					el('label', {}, __('Confirmation Type', 'formlayer')),
					el('select', {
						className: 'formlayer-select',
						value: confirmations.type || 'message',
						onChange: function(e){ patch('confirmations', 'type', e.target.value); }
					},
						el('option', { value: 'message' }, __('Display Message', 'formlayer')),
						el('option', { value: 'redirect' }, __('Redirect to Page/URL', 'formlayer'))
					)
				),
				(confirmations.type === 'redirect') ? el('div', { className: 'formlayer-settings-field' },
					el('label', {}, __('Redirect URL', 'formlayer')),
					el('input', {
						type: 'text',
						className: 'formlayer-input',
						placeholder: 'https://example.com/thanks',
						value: confirmations.redirect_url || '',
						onChange: function(e){ patch('confirmations', 'redirect_url', e.target.value); }
					})
				) : el(Fragment, {},
					el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('Success Message', 'formlayer')),
						el('textarea', {
							className: 'formlayer-input',
							style: { height: '100px' },
							value: confirmations.message || '',
							onChange: function(e){ patch('confirmations', 'message', e.target.value); }
						})
					),
					el('div', { className: 'formlayer-settings-field' },
						el('label', {}, __('Hide form after successful submission', 'formlayer')),
						el('div', { className: 'formlayer-switch-wrapper' },
							el('label', { className: 'formlayer-switch' },
								el('input', {
									type: 'checkbox',
									checked: confirmations.hide_form !== false,
									onChange: function(e){ patch('confirmations', 'hide_form', Boolean(e.target.checked)); }
								}),
								el('span', { className: 'slider round' })
							)
						)
					)
				)
			),
			integrations: el('div', { className: 'formlayer-settings-section active', id: 'formlayer-settings-integrations' },
				el('div', { className: 'section-header' },
					el('h3', {}, __('Integrations', 'formlayer')),
					el('p', {}, __('Connect your form to 3rd party services.', 'formlayer'))
				),
				config.is_pro ? integrationsList() : proLock(
					__('Unlock Pro Integrations', 'formlayer'),
					__('Connect to Slack, Mailchimp, Notion, Trello and more with FormLayer Pro.', 'formlayer')
				)
			),
			custom_css: el('div', { className: 'formlayer-settings-section active', id: 'formlayer-settings-custom_css' },
				el('div', { className: 'section-header' },
					el('h3', {}, __('Custom CSS', 'formlayer')),
					el('p', {}, __('Add custom styles specifically for this form.', 'formlayer'))
				),
				el('div', { className: 'formlayer-settings-field' },
					el('textarea', {
						className: 'formlayer-input',
						style: { height: '300px', fontFamily: 'monospace' },
						placeholder: '.formlayer-form { /* Your styles here */ }',
						value: settings.custom_css || '',
						onChange: function(e){ patchRoot('custom_css', e.target.value); }
					})
				)
			)
		};

		var shortcodeText = shortcode || (displayId ? '[formlayer id="' + displayId + '"]' : '');

		var shortcodeBox = el('div', { className: 'formlayer-header-shortcode-box' },
			el('input', {
				type: 'text',
				value: shortcodeText ? shortcodeText : __('Save first...', 'formlayer'),
				readOnly: true,
				className: 'formlayer-header-shortcode-input',
				onClick: function(e){
					if(shortcodeText){
						e.target.select();
					}
				}
			}),
			el('button', {
				type: 'button',
				className: 'formlayer-header-copy-btn' + (copied ? ' is-copied' : ''),
				disabled: !shortcodeText,
				title: shortcodeText ? __('Copy Shortcode', 'formlayer') : __('Save or publish form first', 'formlayer'),
				onClick: function(){
					if(!shortcodeText){
						return;
					}
					if(navigator.clipboard && navigator.clipboard.writeText){
						navigator.clipboard.writeText(shortcodeText);
					} else {
						var temp = document.createElement('textarea');
						temp.value = shortcodeText;
						document.body.appendChild(temp);
						temp.select();
						document.execCommand('copy');
						document.body.removeChild(temp);
					}
					setCopied(true);
					setTimeout(function(){ setCopied(false); }, 2000);
				}
			},
				el('span', { className: 'dashicons ' + (copied ? 'dashicons-yes' : 'dashicons-admin-page') }),
				el('span', {}, copied ? __('Copied!', 'formlayer') : __('Copy', 'formlayer'))
			)
		);

		var button = el('button', {
			type: 'button',
			className: 'components-button is-secondary formlayer-header-settings-btn',
			onClick: function(){ setOpen(true); }
		},
			el('span', { className: 'dashicons dashicons-admin-settings' }),
			el('span', {}, __('Form Settings', 'formlayer'))
		);

		var headerContent = el('div', { className: 'formlayer-gutenberg-header-actions' },
			shortcodeBox,
			button
		);

		var headerRoot = headerReady ? (document.getElementById('formlayer-header-settings-root') || ensureHeaderButtonRoot()) : null;
		var headerFill = headerRoot && wp.element.createPortal ? wp.element.createPortal(headerContent, headerRoot) : null;

		var modal = (open && wp.element.createPortal) ? wp.element.createPortal(
			el('div', {
				id: 'formlayer-form-settings-modal',
				className: 'formlayer-modal-overlay active',
				onClick: function(e){
					if(e.target && (e.target.classList.contains('formlayer-modal-overlay') || e.target.classList.contains('formlayer-modal-close'))){
						setOpen(false);
					}
				}
			},
				el('div', { className: 'formlayer-modal-panel' },
					el('div', { className: 'formlayer-modal-header' },
						el('div', { className: 'header-left' },
							el('span', { className: 'dashicons dashicons-admin-settings' }),
							el('h2', {}, __('Form Settings', 'formlayer'))
						),
						el('div', { className: 'header-right' },
							el('button', {
								type: 'button',
								className: 'formlayer-btn formlayer-btn-primary',
								id: 'formlayer-settings-apply-btn',
								onClick: function(){ setOpen(false); }
							}, __('Apply Settings', 'formlayer')),
							el('button', {
								type: 'button',
								className: 'formlayer-modal-close',
								id: 'formlayer-settings-close-btn',
								onClick: function(){ setOpen(false); }
							}, '×')
						)
					),
					el('div', { className: 'formlayer-modal-body' },
						el('div', { className: 'formlayer-modal-sidebar' },
							el('ul', {},
								el('li', {
									className: section === 'notifications' ? 'active' : '',
									onClick: function(){ setSection('notifications'); }
								},
									el('span', { className: 'dashicons dashicons-email' }),
									__('Email Notifications', 'formlayer')
								),
								el('li', {
									className: section === 'email_confirmation' ? 'active' : '',
									onClick: function(){ setSection('email_confirmation'); }
								},
									el('span', { className: 'dashicons dashicons-email-alt' }),
									__('Email Confirmation', 'formlayer')
								),
								el('li', {
									className: section === 'confirmations' ? 'active' : '',
									onClick: function(){ setSection('confirmations'); }
								},
									el('span', { className: 'dashicons dashicons-yes' }),
									__('Form Confirmations', 'formlayer')
								),
								el('li', {
									className: section === 'integrations' ? 'active' : '',
									onClick: function(){ setSection('integrations'); }
								},
									el('span', { className: 'dashicons dashicons-admin-links' }),
									__('Integrations', 'formlayer')
								),
								el('li', {
									className: section === 'custom_css' ? 'active' : '',
									onClick: function(){ setSection('custom_css'); }
								},
									el('span', { className: 'dashicons dashicons-editor-code' }),
									__('Custom CSS', 'formlayer')
								)
							)
						),
						el('div', { className: 'formlayer-modal-main' },
							sections[(section && sections[section]) ? section : 'notifications'] || null
						)
					)
				)
			),
			document.body
		) : null;

		return el(Fragment, {}, headerFill, modal);
	}

	function syncCanvasFieldsState(){
		var count = 0;
		if(wp.data && wp.data.select){
			var blockEditor = wp.data.select('core/block-editor');
			if(blockEditor && typeof blockEditor.getBlocks === 'function'){
				var blocks = blockEditor.getBlocks();
				count = blocks ? blocks.length : 0;
			}
		}
		var hasBlocks = count > 0;
		var updateDoc = function(targetDoc){
			if(!targetDoc || !targetDoc.body){
				return;
			}
			targetDoc.body.classList.toggle('formlayer-has-fields', hasBlocks);
			targetDoc.body.classList.toggle('formlayer-is-empty', !hasBlocks);
			var roots = targetDoc.querySelectorAll('.is-root-container, .block-editor-block-list__layout');
			for(var i = 0; i < roots.length; i++){
				roots[i].classList.toggle('formlayer-has-fields', hasBlocks);
				roots[i].classList.toggle('formlayer-is-empty', !hasBlocks);
			}
		};

		updateDoc(document);
		var iframe = getEditorIframe();
		if(iframe && iframe.contentDocument){
			updateDoc(iframe.contentDocument);
		}
	}

	function CanvasStateSync(){
		var blockCount = useSelect ? useSelect(function(select){
			var blockEditor = select('core/block-editor');
			if(!blockEditor){
				return 0;
			}
			var blocks = blockEditor.getBlocks();
			return blocks ? blocks.length : 0;
		}, []) : 0;

		useEffect(function(){
			syncCanvasFieldsState();
		}, [blockCount]);

		return null;
	}

	if(registerPlugin){
		registerPlugin('formlayer-canvas-state-sync', {
			render: CanvasStateSync
		});
	}

	if(registerPlugin){
		registerPlugin('formlayer-left-sidebar', {
			render: FormLayerLeftDock
		});
	}

	if(registerPlugin){
		registerPlugin('formlayer-drop-overlay', {
			render: CanvasDropOverlay
		});
	}

	if(registerPlugin && (useEntityProp || useSelect)){
		registerPlugin('formlayer-form-settings', {
			render: FormSettingsApp
		});
	}

	function injectCanvasStyles(doc){
		if(!doc || !doc.head){
			return;
		}
		var urls = config.style_urls || [];
		urls.forEach(function(item){
			if(!item || !item.href){
				return;
			}
			var id = item.id || item.href;
			if(doc.getElementById(id)){
				return;
			}
			var link = doc.createElement('link');
			link.id = id;
			link.rel = 'stylesheet';
			link.href = item.href;
			doc.head.appendChild(link);
		});
		if(doc.body){
			doc.body.classList.add('formlayer-form-editor');
		}
	}

	function getEditorIframe(){
		return document.querySelector('iframe[name="editor-canvas"], iframe.editor-canvas, iframe.block-editor-iframe__iframe');
	}

	function syncEditorCanvasStyles(){
		document.body.classList.add('formlayer-form-editor');
		injectCanvasStyles(document);
		var iframe = getEditorIframe();
		if(iframe){
			try{
				injectCanvasStyles(iframe.contentDocument);
			}catch(e){}
			if(!iframe.getAttribute('data-formlayer-css')){
				iframe.setAttribute('data-formlayer-css', '1');
				iframe.addEventListener('load', function(){
					try{
						injectCanvasStyles(iframe.contentDocument);
						syncCanvasFieldsState();
					}catch(err){}
				});
			}
		}
		syncCanvasFieldsState();
	}

	if(wp.data && wp.data.subscribe){
		wp.data.subscribe(function(){
			syncCanvasFieldsState();
		});
	}

	if(wp.domReady){
		wp.domReady(function(){
			syncEditorCanvasStyles();
			ensureLeftSidebarRoot();
			syncCanvasFieldsState();
			var tries = 0;
			var timer = setInterval(function(){
				tries++;
				syncEditorCanvasStyles();
				ensureLeftSidebarRoot();
				syncCanvasFieldsState();
				if(getEditorIframe() || tries > 40){
					clearInterval(timer);
					syncEditorCanvasStyles();
					ensureLeftSidebarRoot();
					syncCanvasFieldsState();
				}
			}, 250);
		});
	}
})(window.wp);
