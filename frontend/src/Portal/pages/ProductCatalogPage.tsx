import { useState } from 'react';
import {
  Box, Typography, TextField, Grid, Card, CardContent, CardActions,
  Button, Chip, InputAdornment, CircularProgress,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import { useQuery } from '@tanstack/react-query';
import { productsApi } from '../../shared/api/endpoints';
import type { Product } from '../../shared/types';

export default function ProductCatalogPage() {
  const [search, setSearch] = useState('');

  const { data, isLoading } = useQuery({
    queryKey: ['portal-products', search],
    queryFn: () => productsApi.list({ search, per_page: 50, activo: true }).then(r => r.data),
  });

  const products: Product[] = data?.data || [];

  return (
    <Box>
      <Typography variant="h5" fontWeight={600} gutterBottom>
        Catálogo de Productos
      </Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Explora nuestros productos y realiza tu pedido.
      </Typography>

      <TextField
        fullWidth
        size="small"
        placeholder="Buscar productos..."
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        slotProps={{
          input: {
            startAdornment: <InputAdornment position="start"><SearchIcon /></InputAdornment>,
          },
        }}
        sx={{ mb: 3 }}
      />

      {isLoading ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', py: 8 }}>
          <CircularProgress />
        </Box>
      ) : products.length === 0 ? (
        <Typography color="text.secondary" textAlign="center" sx={{ py: 8 }}>
          No se encontraron productos.
        </Typography>
      ) : (
        <Grid container spacing={2}>
          {products.map((product) => (
            <Grid size={{ xs: 12, sm: 6, md: 4 }} key={product.id}>
              <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <CardContent sx={{ flexGrow: 1 }}>
                  <Typography variant="subtitle1" fontWeight={600} gutterBottom noWrap>
                    {product.nombre}
                  </Typography>
                  {product.descripcion && (
                    <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                      {product.descripcion}
                    </Typography>
                  )}
                  <Box sx={{ display: 'flex', alignItems: 'baseline', gap: 1, mb: 1 }}>
                    <Typography variant="h6" color="primary" fontWeight={700}>
                      S/ {Number(product.precio_venta).toFixed(2)}
                    </Typography>
                    {product.precio_venta_usd > 0 && (
                      <Typography variant="caption" color="text.secondary">
                        ${Number(product.precio_venta_usd).toFixed(2)}
                      </Typography>
                    )}
                  </Box>
                  <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
                    {product.producto_clase && (
                      <Chip label={product.producto_clase.nombre} size="small" variant="outlined" />
                    )}
                    {product.unidad_medida && (
                      <Chip label={product.unidad_medida.simbolo} size="small" color="primary" variant="outlined" />
                    )}
                  </Box>
                  <Box sx={{ mt: 1 }}>
                    <Chip
                      label={product.stock_actual > 0 ? `Stock: ${product.stock_actual}` : 'Sin stock'}
                      size="small"
                      color={product.stock_actual > 0 ? 'success' : 'error'}
                    />
                  </Box>
                </CardContent>
                <CardActions sx={{ px: 2, pb: 2 }}>
                  <Button
                    fullWidth
                    variant="contained"
                    size="small"
                    startIcon={<ShoppingCartIcon />}
                    disabled
                  >
                    Agregar al Pedido
                  </Button>
                </CardActions>
              </Card>
            </Grid>
          ))}
        </Grid>
      )}
    </Box>
  );
}
