import { Box, Grid, Card, CardContent, Typography } from '@mui/material';
import PeopleIcon from '@mui/icons-material/People';
import InventoryIcon from '@mui/icons-material/Inventory';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import AccountBalanceIcon from '@mui/icons-material/AccountBalance';
import WarningAmberIcon from '@mui/icons-material/WarningAmber';

const stats = [
  { label: 'Clientes', value: '1,284', icon: <PeopleIcon />, color: '#1565c0' },
  { label: 'Productos', value: '3,647', icon: <InventoryIcon />, color: '#2e7d32' },
  { label: 'Ventas del Mes', value: 'S/ 284,500', icon: <ShoppingCartIcon />, color: '#6a1b9a' },
  { label: 'Ventas Pendientes', value: '23', icon: <WarningAmberIcon />, color: '#e65100' },
  { label: 'Stock Bajo', value: '12', icon: <AccountBalanceIcon />, color: '#c62828' },
];

export default function AdminDashboardPage() {
  return (
    <Box>
      <Typography variant="h5" gutterBottom>Dashboard</Typography>
      <Grid container spacing={3}>
        {stats.map((stat) => (
          <Grid key={stat.label} size={{ xs: 12, sm: 6, md: 3 }}>
            <Card>
              <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                <Box sx={{ color: stat.color }}>{stat.icon}</Box>
                <Box>
                  <Typography variant="h6" fontWeight={600}>{stat.value}</Typography>
                  <Typography variant="body2" color="text.secondary">{stat.label}</Typography>
                </Box>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>
    </Box>
  );
}
