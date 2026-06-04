import { useState } from 'react';
import { Outlet, useNavigate, useLocation, Link as RouterLink } from 'react-router-dom';
import { ThemeProvider } from '@mui/material/styles';
import { AppBar, Toolbar, Typography, Container, Box, IconButton, Badge, Avatar, Menu, MenuItem, BottomNavigation, BottomNavigationAction, Divider, ListItemIcon, useMediaQuery } from '@mui/material';
import NotificationsIcon from '@mui/icons-material/Notifications';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import StorefrontIcon from '@mui/icons-material/Storefront';
import DashboardIcon from '@mui/icons-material/Dashboard';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import LogoutIcon from '@mui/icons-material/Logout';
import { portalTheme } from '../../shared/theme';
import { useAuthStore } from '../../shared/hooks/useAuth';

const navItems = [
  { label: 'Productos', icon: <StorefrontIcon />, path: '/portal/productos' },
  { label: 'Dashboard', icon: <DashboardIcon />, path: '/portal/dashboard' },
];

export default function PortalLayout() {
  const navigate = useNavigate();
  const location = useLocation();
  const isMobile = useMediaQuery('(max-width:600px)');
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const user = useAuthStore((s) => s.user);
  const logout = useAuthStore((s) => s.logout);

  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);

  const currentTab = navItems.findIndex((item) => location.pathname.startsWith(item.path));
  const activeTab = currentTab >= 0 ? currentTab : 0;

  const handleLogout = () => {
    setAnchorEl(null);
    logout();
    navigate('/portal/productos');
  };

  return (
    <ThemeProvider theme={portalTheme}>
      <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh', pb: isMobile ? 7 : 0 }}>
        <AppBar position="sticky" elevation={1} color="default" sx={{ bgcolor: 'white' }}>
          <Toolbar>
            <Typography
              variant="h6"
              fontWeight={700}
              color="primary"
              sx={{ flexGrow: 1, cursor: 'pointer' }}
              onClick={() => navigate('/portal/productos')}
            >
              ARMORA
            </Typography>
            {!isMobile && (
              <Box sx={{ display: 'flex', gap: 1, mr: 2 }}>
                {navItems.map((item) => (
                  <Typography
                    key={item.path}
                    component={RouterLink}
                    to={item.path}
                    sx={{
                      textDecoration: 'none',
                      color: location.pathname.startsWith(item.path) ? 'primary.main' : 'text.secondary',
                      fontWeight: location.pathname.startsWith(item.path) ? 600 : 400,
                      px: 1.5,
                      py: 0.5,
                      borderRadius: 1,
                      '&:hover': { bgcolor: 'action.hover' },
                    }}
                  >
                    {item.label}
                  </Typography>
                ))}
              </Box>
            )}
            <IconButton color="inherit" size="small" sx={{ mr: 0.5 }}>
              <Badge badgeContent={0} color="secondary">
                <ShoppingCartIcon />
              </Badge>
            </IconButton>
            <IconButton color="inherit" size="small" sx={{ mr: 0.5 }}>
              <Badge badgeContent={0} color="secondary">
                <NotificationsIcon />
              </Badge>
            </IconButton>
            {isAuthenticated ? (
              <>
                <IconButton onClick={(e) => setAnchorEl(e.currentTarget)} size="small" sx={{ ml: 0.5 }}>
                  <Avatar sx={{ width: 32, height: 32, bgcolor: 'primary.main', fontSize: 14 }}>
                    {(user?.nombre_completo || 'C').charAt(0).toUpperCase()}
                  </Avatar>
                </IconButton>
                <Menu
                  anchorEl={anchorEl}
                  open={Boolean(anchorEl)}
                  onClose={() => setAnchorEl(null)}
                  transformOrigin={{ horizontal: 'right', vertical: 'top' }}
                  anchorOrigin={{ horizontal: 'right', vertical: 'bottom' }}
                >
                  <MenuItem disabled>
                    <Typography variant="body2" fontWeight={600}>{user?.nombre_completo || 'Cliente'}</Typography>
                  </MenuItem>
                  <Divider />
                  <MenuItem onClick={handleLogout}>
                    <ListItemIcon><LogoutIcon fontSize="small" /></ListItemIcon>
                    Cerrar Sesión
                  </MenuItem>
                </Menu>
              </>
            ) : (
              <IconButton color="primary" onClick={() => navigate('/portal/login')} size="small">
                <AccountCircleIcon />
              </IconButton>
            )}
          </Toolbar>
        </AppBar>

        <Container maxWidth="lg" sx={{ flex: 1, py: 3 }}>
          <Outlet />
        </Container>

        <Box component="footer" sx={{ py: 2, textAlign: 'center', bgcolor: 'grey.100', display: { xs: 'none', sm: 'block' } }}>
          <Typography variant="caption" color="text.secondary">
            &copy; {new Date().getFullYear()} ARMORA SAC &mdash; Todos los derechos reservados
          </Typography>
        </Box>

        {isMobile && (
          <BottomNavigation
            value={activeTab}
            onChange={(_, index) => navigate(navItems[index].path)}
            sx={{ position: 'fixed', bottom: 0, left: 0, right: 0, borderTop: 1, borderColor: 'divider', bgcolor: 'white', zIndex: 1200 }}
          >
            {navItems.map((item) => (
              <BottomNavigationAction key={item.path} label={item.label} icon={item.icon} />
            ))}
          </BottomNavigation>
        )}
      </Box>
    </ThemeProvider>
  );
}
