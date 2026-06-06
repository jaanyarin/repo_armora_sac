import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  AppBar,
  Toolbar,
  Typography,
  IconButton,
  Avatar,
  Menu,
  MenuItem,
  Divider,
  Button,
  Box,
} from '@mui/material';
import MenuIcon from '@mui/icons-material/Menu';
import { useAuthStore } from '../../shared/hooks/useAuth';

interface TopbarProps {
  title: string;
  path: string;
  onToggleSidebar: () => void;
}

export default function Topbar({ title, path, onToggleSidebar }: TopbarProps) {
  const navigate = useNavigate();
  const { user, logout } = useAuthStore();
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);

  return (
    <AppBar
      position="fixed"
      elevation={1}
      sx={{
        bgcolor: '#ffffff',
        color: '#111827',
        width: { md: `calc(100% - 280px)` },
        ml: { md: `280px` },
      }}
    >
      <Toolbar sx={{ minHeight: 62 }}>
        <IconButton
          edge="start"
          color="inherit"
          sx={{ mr: 1, display: { md: 'none' } }}
          onClick={onToggleSidebar}
        >
          <MenuIcon />
        </IconButton>

        <Box sx={{ flexGrow: 1 }}>
          <Typography variant="h6" fontSize={18} fontWeight={600} noWrap>
            {title || 'ARMORA'}
          </Typography>
          <Typography variant="caption" color="text.secondary" sx={{ display: { xs: 'none', sm: 'block' } }}>
            Ruta: {path}
          </Typography>
        </Box>

        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
          <Button
            variant="text"
            size="small"
            onClick={() => navigate('/admin/dashboard')}
            sx={{ color: '#374151', textTransform: 'none', display: { xs: 'none', sm: 'inline-flex' } }}
          >
            Home
          </Button>
          <Button
            variant="text"
            size="small"
            onClick={() => navigate('/admin/clientes')}
            sx={{ color: '#374151', textTransform: 'none', display: { xs: 'none', md: 'inline-flex' } }}
          >
            Mi Perfil
          </Button>

          <Avatar
            sx={{
              bgcolor: '#f3f4f6',
              color: '#111827',
              cursor: 'pointer',
              width: 36,
              height: 36,
              fontSize: 13,
              fontWeight: 700,
              border: '1px solid #e5e7eb',
            }}
            onClick={(e) => setAnchorEl(e.currentTarget)}
          >
            {user?.nombre_completo?.charAt(0)?.toUpperCase() || 'U'}
          </Avatar>

          <Menu
            anchorEl={anchorEl}
            open={!!anchorEl}
            onClose={() => setAnchorEl(null)}
            slotProps={{
              paper: {
                sx: { minWidth: 180, mt: 0.5, borderRadius: 2, boxShadow: '0 8px 20px rgba(0,0,0,0.12)' },
              },
            }}
          >
            <MenuItem disabled sx={{ opacity: '1 !important' }}>
              <Typography variant="body2" fontWeight={600}>
                {user?.nombre_completo || 'Usuario'}
              </Typography>
            </MenuItem>
            <Divider />
            <MenuItem
              onClick={() => {
                setAnchorEl(null);
                navigate('/admin/dashboard');
              }}
            >
              Home
            </MenuItem>
            <MenuItem
              onClick={() => {
                setAnchorEl(null);
                navigate('/admin/clientes');
              }}
            >
              Perfil Personal
            </MenuItem>
            <MenuItem
              onClick={() => {
                setAnchorEl(null);
                navigate('/admin/configuracion');
              }}
            >
              Preferencias
            </MenuItem>
            <Divider />
            <MenuItem
              onClick={() => {
                setAnchorEl(null);
                logout();
              }}
              sx={{ color: '#c62828' }}
            >
              Cerrar Sesión
            </MenuItem>
          </Menu>
        </Box>
      </Toolbar>
    </AppBar>
  );
}
