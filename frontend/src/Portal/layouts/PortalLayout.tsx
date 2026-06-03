import { Outlet } from 'react-router-dom';
import { AppBar, Toolbar, Typography, Container, Box, IconButton, Badge } from '@mui/material';
import NotificationsIcon from '@mui/icons-material/Notifications';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';

export default function PortalLayout() {
  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      <AppBar position="sticky" elevation={1} color="default" sx={{ bgcolor: 'white' }}>
        <Toolbar>
          <Typography variant="h6" fontWeight={700} color="primary" sx={{ flexGrow: 1 }}>
            ARMORA
          </Typography>
          <IconButton color="inherit">
            <Badge badgeContent={3} color="secondary">
              <NotificationsIcon />
            </Badge>
          </IconButton>
          <IconButton color="inherit" sx={{ ml: 1 }}>
            <Badge badgeContent={1} color="secondary">
              <ShoppingCartIcon />
            </Badge>
          </IconButton>
        </Toolbar>
      </AppBar>
      <Container maxWidth="lg" sx={{ flex: 1, py: 3 }}>
        <Outlet />
      </Container>
      <Box component="footer" sx={{ py: 2, textAlign: 'center', bgcolor: 'grey.100' }}>
        <Typography variant="caption" color="text.secondary">
          &copy; {new Date().getFullYear()} ARMORA SAC &mdash; Todos los derechos reservados
        </Typography>
      </Box>
    </Box>
  );
}
