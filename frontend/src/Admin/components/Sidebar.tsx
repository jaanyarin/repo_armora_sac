import { useState, useMemo, useRef, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  Box,
  Typography,
  TextField,
  InputAdornment,
  IconButton,
  List,
  ListItemButton,
  ListItemText,
  Collapse,
  Divider,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import ExpandLess from '@mui/icons-material/ExpandLess';
import ExpandMore from '@mui/icons-material/ExpandMore';
import { sidebarMenu, type SidebarSection } from '../data/sidebarMenu';

const SIDEBAR_BG = '#1f2937';
const SIDEBAR_HOVER = '#374151';
const SIDEBAR_ACTIVE = '#374151';
const ITEM_BG = '#273449';
const TEXT_PRIMARY = '#ffffff';

interface SidebarProps {
  onClose: () => void;
}

export default function Sidebar({ onClose }: SidebarProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const [search, setSearch] = useState('');
  const searchRef = useRef<HTMLInputElement>(null);

  const [openSection, setOpenSection] = useState<string | null>(null);

  const filteredMenu = useMemo(() => {
    if (!search.trim()) return sidebarMenu;
    const q = search.trim().toLowerCase();
    return sidebarMenu
      .map((section) => {
        const sectionMatch = section.title.toLowerCase().includes(q);
        const filteredItems = section.items.filter((item) =>
          item.label.toLowerCase().includes(q),
        );
        if (sectionMatch || filteredItems.length > 0) {
          return {
            ...section,
            items: sectionMatch ? section.items : filteredItems,
          };
        }
        return null;
      })
      .filter(Boolean) as SidebarSection[];
  }, [search]);

  const isSearching = search.trim().length > 0;

  const toggleSection = (title: string) => {
    setOpenSection((prev) => (prev === title ? null : title));
  };

  useEffect(() => {
    if (isSearching) return;
    const active = sidebarMenu.find((section) =>
      section.items.some((item) => location.pathname === item.path || location.pathname.startsWith(item.path + '/')),
    );
    if (active) setOpenSection(active.title);
  }, [location.pathname, isSearching]);

  const isActive = (path: string) =>
    location.pathname === path || location.pathname.startsWith(path + '/');

  const handleItemClick = (item: { label: string; path: string }) => {
    navigate(item.path);
    onClose();
  };

  return (
    <Box
      sx={{
        width: 280,
        height: '100%',
        display: 'flex',
        flexDirection: 'column',
        bgcolor: SIDEBAR_BG,
        color: TEXT_PRIMARY,
      }}
    >
      <Box sx={{ px: 2, py: 2.5, bgcolor: '#111827', textAlign: 'center' }}>
        <Typography variant="h6" fontWeight={700} letterSpacing={1}>
          ARMORA ERP
        </Typography>
      </Box>

      <Box sx={{ px: 2, py: 1.5 }}>
        <TextField
          inputRef={searchRef}
          placeholder="Buscar módulo..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          variant="outlined"
          size="small"
          fullWidth
          slotProps={{
            input: {
              startAdornment: (
                <InputAdornment position="start">
                  <SearchIcon sx={{ color: '#9ca3af', fontSize: 20 }} />
                </InputAdornment>
              ),
              endAdornment: search ? (
                <InputAdornment position="end">
                  <IconButton
                    size="small"
                    onClick={() => {
                      setSearch('');
                      searchRef.current?.focus();
                    }}
                    sx={{ color: '#9ca3af' }}
                  >
                    <Typography sx={{ fontSize: 18, lineHeight: 1 }}>×</Typography>
                  </IconButton>
                </InputAdornment>
              ) : undefined,
            },
          }}
          sx={{
            '& .MuiOutlinedInput-root': {
              bgcolor: '#374151',
              color: '#e5e7eb',
              borderRadius: 1.5,
              '& fieldset': { borderColor: 'transparent' },
              '&:hover fieldset': { borderColor: '#4b5563' },
              '&.Mui-focused fieldset': { borderColor: '#6b7280' },
            },
          }}
        />
      </Box>

      <Box sx={{ flex: 1, overflowY: 'auto', overflowX: 'hidden' }}>
        {filteredMenu.length === 0 ? (
          <Typography sx={{ px: 3, py: 3, color: '#9ca3af', fontSize: 14 }}>
            No se encontraron módulos para: <strong>{search}</strong>
          </Typography>
        ) : (
          filteredMenu.map((section) => {
            const isOpen = isSearching || openSection === section.title;

            return (
              <Box key={section.title}>
                <ListItemButton
                  onClick={() => {
                    if (!isSearching) toggleSection(section.title);
                  }}
                  sx={{
                    px: 2.5,
                    py: 1.5,
                    bgcolor: isOpen ? SIDEBAR_ACTIVE : 'transparent',
                    '&:hover': { bgcolor: SIDEBAR_HOVER },
                  }}
                >
                  <ListItemText
                    primary={section.title}
                    primaryTypographyProps={{
                      fontSize: 14,
                      fontWeight: 700,
                      color: TEXT_PRIMARY,
                    }}
                  />
                  {isOpen ? (
                    <ExpandLess sx={{ fontSize: 20, color: '#9ca3af' }} />
                  ) : (
                    <ExpandMore sx={{ fontSize: 20, color: '#9ca3af' }} />
                  )}
                </ListItemButton>

                <Collapse in={isOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ bgcolor: ITEM_BG }}>
                    {section.items.map((item) => (
                      <ListItemButton
                        key={item.path}
                        selected={isActive(item.path)}
                        onClick={() => handleItemClick(item)}
                        sx={{
                          pl: 4,
                          pr: 2,
                          py: 1,
                          '&.Mui-selected': {
                            bgcolor: '#3b4a60',
                            '&:hover': { bgcolor: '#3b4a60' },
                          },
                          '&:hover': { bgcolor: SIDEBAR_HOVER },
                        }}
                      >
                        <ListItemText
                          primary={item.label}
                          primaryTypographyProps={{
                            fontSize: 13.5,
                            color: isActive(item.path) ? '#ffffff' : '#d1d5db',
                          }}
                        />
                      </ListItemButton>
                    ))}
                  </List>
                </Collapse>

                <Divider sx={{ borderColor: '#374151' }} />
              </Box>
            );
          })
        )}
      </Box>
    </Box>
  );
}
