import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import '@mdi/font/css/materialdesignicons.css'

Vue.use(Vuetify)

export default new Vuetify({
  theme: {
    dark: false,
    themes: {
      light: {
        dark: '#000000',
        'dark-1': '#E0E0E0',
        'dark-2': '#333333',

        secondary: '#BDBDBD',
        'secondary-1' : '#424143',
        'secondary-2': '#828282',
        'secondary-3': '#545454',
        'secondary-4': '#BDBDBD',
        'secondary-5': '#F2F2F2',
        
        error: '#B52210',
        danger: '#F8835E',
        'danger-1': '#FF4444',
        success: '#85C751',
        'success-1': '#46B254',
        'success-2': '#9DD776',
        warning: '#ffb624',
        'warning-1': '#FEDA74',
        'warning-2': '#FFF5D8',
        'warning-3': '#FFF1DC',
        primary: '#C94104',
        'primary-1': '#D9DFF6',
        'primary-2': '#C9410433',

        'app-body': '#F6F6F6',

        //branding
        'navigation_background_color': '#FFFFFF',
        'navigation_banner_color': '#FFFFFF',
        'navigation_link_color': '#C94104',
        'navigation_link_background_color': '#C9410433',
        'navigation_text_icon_color': '#C94104',
        'navigation_link_inactive_color': '#828282',
        'sidebar_color': '#C94104',
        'default_color_1': '#757575',

        'mini_navigation_background_color': '#FFFFFF',
        'mini_navigation_link_color': '#C94104',
        'mini_navigation_link_background_color': '#C9410433',
        'mini_navigation_icon_color': '#C94104',
        'mini_navigation_inactive_link_color': '#F2F2F2',

        //landing-page
        'l-primary': '#E05835',
        
      },
      dark: {
        dark: '#000000',
        'dark-1': '#E0E0E0',
        'dark-2': '#111421',
        light: '#ffffff',

        secondary: '#BDBDBD',
        'secondary-1' : '#424143',
        'secondary-2': '#828282',
        'secondary-3': '#545454',
        'secondary-4': '#BDBDBD',
        'secondary-5': '#F2F2F2',
        
        error: '#B52210',
        danger: '#F8835E',
        'danger-1': '#FF4444',
        success: '#85C751',
        'success-1': '#46B254',
        'success-2': '#9DD776',
        warning: '#FFC624',
        'warning-1': '#FEDA74',
        'warning-2': '#FFF5D8',
        'warning-3': '#F2C94C',
        primary: '#C94104',
        'primary-1': '#D9DFF6',

        'app-body': '#0f1726',

        //branding
        '--primary-brand-color': '#FFA500',

        'primary_buttons_color': '#C94104',
      },
    },
  },
})