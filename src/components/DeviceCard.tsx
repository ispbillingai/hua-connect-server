
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CircleIcon, Settings2 } from "lucide-react";

interface DeviceCardProps {
  device: {
    id: string;
    serialNumber: string;
    model: string;
    status: "online" | "offline" | "provisioning";
    lastContact: string;
    ipAddress: string;
  };
}

export const DeviceCard = ({ device }: DeviceCardProps) => {
  const getStatusColor = (status: string) => {
    switch (status) {
      case "online":
        return "text-success-dark";
      case "offline":
        return "text-error-dark";
      default:
        return "text-warning-dark";
    }
  };

  return (
    <Card className="p-6 hover:shadow-lg transition-shadow animate-fade-up">
      <div className="flex justify-between items-start mb-4">
        <div>
          <div className="flex items-center gap-2 mb-2">
            <CircleIcon className={`h-3 w-3 ${getStatusColor(device.status)}`} />
            <Badge variant="outline" className="capitalize">
              {device.status}
            </Badge>
          </div>
          <h3 className="font-semibold text-lg mb-1">{device.model}</h3>
          <p className="text-sm text-muted-foreground">{device.serialNumber}</p>
        </div>
        <button className="hover:bg-accent p-2 rounded-full transition-colors">
          <Settings2 className="h-5 w-5" />
        </button>
      </div>
      <div className="space-y-1">
        <p className="text-sm">
          <span className="text-muted-foreground">Last Contact:</span>{" "}
          {device.lastContact}
        </p>
        <p className="text-sm">
          <span className="text-muted-foreground">IP Address:</span>{" "}
          {device.ipAddress}
        </p>
      </div>
    </Card>
  );
};
