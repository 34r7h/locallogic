(
	function(){
		
			
		tinymce.create(
			"tinymce.plugins.USERSULTRAShortcodes",
			{
				init: function(d,e) {},
				createControl:function(d,e)
				{
				
					if(d=="uultra_shortcodes_button"){
					
						d=e.createMenuButton( "uultra_shortcodes_button",{
							title:"Insert Users Ultra Shortcodes",
							icons:false
							});
							
							var a=this;d.onRenderMenu.add(function(c,b){
								
								c=b.addMenu({title:"Login Forms"});
								
								   a.addImmediate(c,"Basic Login Form", '[usersultra_login ]');									
								   a.addImmediate(c,"Login Form with Custom Redirect", '[usersultra_login  redirect_to="http://url_here"]');									
									
																		
								b.addSeparator();		
								
								c=b.addMenu({title:"Registration Forms"});
									a.addImmediate(c,"Front-end Registration Form", '[usersultra_registration]');
								
								b.addSeparator();								
									
								c=b.addMenu({title:"Members Directory"});
									a.addImmediate(c,"Basic Directory", "[usersultra_directory list_per_page=6 optional_fields_to_display='social,rating,country,description' display_country_flag='both' pic_boder_type='rounded']");
								b.addSeparator();
									
								c=b.addMenu({title:"Image Grids"});
									a.addImmediate(c,"Basic Directory", "[usersultra_directory list_per_page=6 optional_fields_to_display='social,rating,country,description' display_country_flag='both' pic_boder_type='rounded']");
								
								
								b.addSeparator();
									
								c=b.addMenu({title:"Users"});
								a.addImmediate(c,"Featured Users", "[usersultra_users_featured users_list='55,59,60' optional_fields_to_display='rating'] ");
								a.addImmediate(c,"Top Rated Users", "[usersultra_users_top_rated optional_fields_to_display='friend,rating,social,country'  display_country_flag='both'] ");
								a.addImmediate(c,"Most Visited Users", "[usersultra_users_most_visited optional_fields_to_display='friend,social' pic_size='80' ]");
								
								a.addImmediate(c,"User Spotlight ", "[usersultra_users_promote optional_fields_to_display='rating,social' users_list='59'  display_country_flag='both'] ");
								a.addImmediate(c,"Latest Users", "[usersultra_users_latest optional_fields_to_display='social' ]");
								a.addImmediate(c,"Custom User Profiles", "[usersultra_profile template_width='80%' user_id= '59' pic_boder_type='rounded' pic_type='avatar' optional_fields_to_display='rating,social,country,description' display_country_flag='both' display_private_message='yes' ] ");
								
								b.addSeparator();
									
								c=b.addMenu({title:"Images"});
								a.addImmediate(c,"Top Rated Photos", "[usersultra_photo_top_rated ] ");
								a.addImmediate(c,"Photo Spotlights", "[usersultra_photos_promote photo_list='95,91' photo_type='photo_large']");
								a.addImmediate(c,"Latest Uploaded Photos", "[usersultra_photo_latest] ");
								
								b.addSeparator();	
									
							
								c=b.addMenu({title:"Pricing Tables"});
									a.addImmediate(c,"One Column", pricing_one_col);
									a.addImmediate(c,"Two Columns", pricing_two_col);
									a.addImmediate(c,"Three Columns", pricing_three_col);
									a.addImmediate(c,"Four Columns", pricing_four_col);																	
								
																	
								b.addSeparator();		
								
								c=b.addMenu({title:"Grids"});
									a.addImmediate(c,"Two Columns", grid_two_col);
									a.addImmediate(c,"Three Columns", grid_three_col);
									a.addImmediate(c,"Four Columns",grid_four_col);
									a.addImmediate(c,"Six Columns", grid_six_col);
									
								b.addSeparator();	
															
																
										
								
											
									
									
									
							});
						return d
					
					} // End IF Statement
					
					return null
				},
		
				addImmediate:function(d,e,a){d.add({title:e,onclick:function(){tinyMCE.activeEditor.execCommand( "mceInsertContent",false,a)}})}
				
			}
		);
		
		tinymce.PluginManager.add( "USERSULTRAShortcodes", tinymce.plugins.USERSULTRAShortcodes);
	}
)();